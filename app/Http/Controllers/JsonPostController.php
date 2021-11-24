<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Http\Resources\BlogPostResource;

class JsonPostController
{
    public function index()
    {
        return BlogPostResource::collection(BlogPost::all());
    }

    public function show(BlogPost $post)
    {
        return BlogPostResource::make($post)->resolve();
    }
}
