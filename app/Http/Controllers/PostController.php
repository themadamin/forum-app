<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        $posts = Post::with('user')->latest()->paginate();

        return Inertia::render(
            'Posts/Index',
            ['posts' => PostResource::collection($posts)]
        );
    }

    public function show(Post $post): Response
    {
        $comments = CommentResource::collection($post->comments()
            ->with('user')
            ->latest()
            ->latest('id')
            ->paginate(10)
        );

        return Inertia::render('Posts/Show',
            [
                'post'     => fn() => PostResource::make($post->load('user')),
                'comments' => fn() => $comments
            ]
        );
    }
}
