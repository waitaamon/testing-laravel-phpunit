<?php

namespace Tests\Feature\Blog;

use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\ExternalPostSuggestionController;
use App\Mail\ExternalPostSuggestedMail;
use App\Models\ExternalPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExternalPostSuggestionTest extends TestCase
{
    public function test_external_post_can_be_submitted()
    {
        $user = User::factory()->create();

        Mail::fake();

        $this->post(action(ExternalPostSuggestionController::class), [
            'title' => 'test',
            'url' => 'https://abc.com'
        ])
        ->assertRedirect(action([BlogPostController::class, 'index']))
        ->assertSessionHas('laravel_flash_message');

        Mail::assertSent(function (ExternalPostSuggestedMail $mail) use ($user){
            return $mail->to[0]['address'] === $user->email;
        });

        $this->assertDatabaseHas(ExternalPost::class, [
            'title' => 'test',
            'url' => 'https://abc.com'
        ]);
    }
}
