<?php

namespace Tests\Feature\Blog;

use App\Http\Controllers\BlogPostAdminController;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BlogPostAdminControllerTest extends TestCase
{
    public function test_only_a_logged_in_user_can_make_change_to_a_post()
    {
        $post = BlogPost::factory()->create();

        $sendRequest = fn() => $this->post(action([BlogPostAdminController::class, 'update'], $post), [
            'title' => 'test',
            'author' => $post->author,
            'body' => $post->body,
            'date' => $post->date->format('Y-m-d'),
        ]);

        $sendRequest()->assertRedirect(route('login'));

        $this->assertNotEquals('test', $post->refresh()->title);

        $this->login();

        $sendRequest();

        $this->assertEquals('test', $post->refresh()->title);
    }
}
