<?php

namespace Tests\Console\Commands;

use App\Console\Commands\SyncExternalPostsCommand;
use App\Models\ExternalPost;
use Tests\Fakes\RssRepositoryFake;
use Tests\TestCase;

class SyncExternalPostsCommandTest extends TestCase
{
    public function test_sync_several_repositories_at_once()
    {

        RssRepositoryFake::setUp();

        $urls = [
            'https://test-a.com',
            'https://test-b.com',
            'https://test-c.com',
        ];

        config()->set('services.external_feeds', $urls);

        $this->artisan('sync:externals')->assertExitCode(0);

        $this->assertEquals($urls, RssRepositoryFake::getUrls());

        $this->assertDatabaseCount(ExternalPost::class, 3);
    }
}
