<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Policies\CommentPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Http\Request;

#[UsePolicy(CommentPolicy::class)]
class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate(['body' => ['required', 'string']]);

        Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $post->id,
            'body'    => $request->input('body'),
        ]);

        return redirect($post->showRoute())
            ->banner('Comment added.');
    }

    public function update(Request $request, Comment $comment)
    {
        $request->user()->can('update', $comment);
        $data = $request->validate(['body' => ['required', 'string', 'max:2500']]);

        $comment->update($data);

        return redirect($comment->post->showRoute(['page' => $request->query('page')]))
            ->banner('Comment updated.');
    }

    public function destroy(Request $request, Comment $comment)
    {
        $request->user()->can('delete', $comment);
        $comment->delete();

        return to_route('posts.show',
            [
                'post' => $comment->post_id,
                'page' => $request->query('page'),
            ])->banner('Comment deleted.');
    }
}
