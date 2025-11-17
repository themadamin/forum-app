<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

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

        return to_route('posts.show', $post)
            ->banner('Comment added.');
    }

    public function update(Request $request, Comment $comment)
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2500']]);

        $comment->update($data);

        return to_route('posts.show',
            ['post' => $comment->post_id, 'page' => $request->query('page')])
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
