<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Topic $topic): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $topic->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Comment added.');
    }

    public function destroy(Request $request, Topic $topic, Comment $comment): RedirectResponse
    {
        // Safety: ensure comment belongs to the topic
        if ($comment->topic_id !== $topic->id) {
            abort(404);
        }

        // Only the comment author can delete their comment
        if ($comment->user_id !== $request->user()->id) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
