<?php

namespace YorCreative\Scrubber\Test\Unit\Services;

use GuzzleHttp\Psr7\Response;
use YorCreative\Scrubber\Repositories\RegexRepository;
use YorCreative\Scrubber\Services\ScrubberService;
use YorCreative\Scrubber\Tests\TestCase;

class ScrubberServiceTest extends TestCase
{
    /**
     * @test
     *
     * @group ScrubberService
     * @group Unit
     */
    public function it_can_encode_a_record()
    {
        $this->assertIsString(ScrubberService::encodeRecord($this->record));
    }

    /**
     * @test
     *
     * @group ScrubberService
     * @group Unit
     */
    public function it_can_decode_an_encoded_record()
    {
        $this->assertIsArray(ScrubberService::decodeRecord('{"test": "test"}'));
    }

    /**
     * @test
     *
     * @group ScrubberService
     * @group Unit
     */
    public function it_can_auto_sanitize_a_record()
    {
        $mockSecretsResponse = $this->getMockFor('get_gitlab_variables');

        $this->createGitlabClientMock([
            new Response(200, [], $mockSecretsResponse),
        ]);

        $content = json_encode(array_merge($this->record['context'], [
            'some' => 'context',
            'nested' => [
                'randomly' => 'nested',
                'array' => [
                    'testing' => 'test',
                    'google_api' => app(RegexRepository::class)->getRegexCollection()->get('google_api')->getTestableString(),
                ],
            ],
        ]));

        ScrubberService::autoSanitize($content);

        $this->assertStringContainsString(config('scrubber.redaction'), $content);
    }
}
