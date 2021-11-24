<?php

namespace Tests\Feature\Blog;

use App\Models\BlogPost;
use App\Models\BlogPostLike;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BlogPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_with_factories()
    {
        $post = BlogPost::factory()
            ->has(BlogPostLike::factory()->count(5), 'postLikes')
            ->create();

        $this->assertCount(5, $post->postLikes);

        $postLike = BlogPostLike::factory()
            ->for(BlogPost::factory()->published())
            ->create();

        $this->assertTrue($postLike->blogPost->isPublished());
    }
}
