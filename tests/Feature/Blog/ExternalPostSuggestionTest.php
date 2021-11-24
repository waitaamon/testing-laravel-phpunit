<?php

namespace Tests\Feature\Blog;

use App\Http\Controllers\ExternalPostSuggestionController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExternalPostSuggestionTest extends TestCase
{
    public function test_external_post_can_be_submitted()
    {
        $this->post(action(ExternalPostSuggestionController::class), [
            'title' => 'test',
            'url' => 'https://abc.com'
        ])
        ->assertSuccessful();
    }
}
