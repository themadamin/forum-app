<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\TopicResource;
use App\Models\Post;
use App\Models\Topic;
use App\Policies\PostPolicy;
use Illuminate\Contracts\Database\Query\Builder;
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
    public function index(Request $request, ?Topic $topic = null)
    {
        if ($request->query('query')) {
            $posts = Post::search($request->query('query'))
                ->query(fn(Builder $query) => $query->with(['user', 'topic']))
                ->when($topic, fn(\Laravel\Scout\Builder $query) => $query->where('topic_id', $topic->id));
        } else {
            $posts = Post::with(['user', 'topic'])
                ->when($topic, fn(Builder $query) => $query->whereBelongsTo($topic))
                ->latest()
                ->latest('id');
        }

        return Inertia::render(
            'Posts/Index',
            [
                'posts' => PostResource::collection($posts->paginate()->withQueryString()),
                'topics' => fn() => TopicResource::collection(Topic::all()),
                'selectedTopic' => fn() => $topic ? TopicResource::make($topic) : null,
                'query' => $request->query('query'),
            ]
        );
    }

    public function show(Request $request, Post $post): Response|RedirectResponse|Redirector
    {
        if (!Str::endsWith($post->showRoute(), $request->path())) {
            return redirect($post->showRoute($request->query()), status: 301);
        }
        $post->load(['user', 'topic']);

        $comments = CommentResource::collection($post->comments()->with('user')->latest()->latest('id')->paginate(10));
        return Inertia::render('Posts/Show',
            [
                'post' => fn () => PostResource::make($post)->withLikePermission(),
                'comments' => function () use ($comments) {
                    $comments->collection->transform(fn($resource) => $resource->withLikePermission());
                    return $comments;
                },
            ]
        );
    }

    public function create()
    {
        return inertia('Posts/Create', [
            'topics' => fn () => TopicResource::collection(Topic::all()),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:10', 'max:120'],
            'topic_id' => ['required', 'exists:topics,id'],
            'body'  => ['required', 'string', 'min:100', 'max:10000'],
        ]);

        $post = Post::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return to_route('posts.show', $post);
    }
}
