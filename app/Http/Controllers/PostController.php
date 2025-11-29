<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Policies\PostPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

#[UsePolicy(PostPolicy::class)]
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

    public function show(Request $request, Post $post): Response|RedirectResponse|Redirector
    {
        if (! Str::contains($post->showRoute(), $request->path())) {
            return redirect($post->showRoute($request->query()), status: 301);
        }

        $comments = CommentResource::collection($post->comments()
            ->with('user')
            ->latest()
            ->latest('id')
            ->paginate(10)
        );

        return Inertia::render('Posts/Show',
            [
                'post'     => fn() => PostResource::make($post->load('user')),
                'comments' => fn() => $comments,
            ]
        );
    }

    public function create()
    {
        return inertia('Posts/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:10', 'max:120'],
            'body' => ['required', 'string', 'min:100', 'max:10000'],
        ]);

        $post = Post::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return to_route('posts.show', $post);
    }
}
