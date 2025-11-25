<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TopicLikeController extends Controller
{
    public function store(Request $request, Topic $topic): RedirectResponse
    {
        $user = $request->user();

        $user->likes()->firstOrCreate([
            'topic_id' => $topic->id,
        ]);

        return back()->with('success', 'Topic liked.');
    }

    public function destroy(Request $request, Topic $topic): RedirectResponse
    {
        $user = $request->user();

        $user->likes()
            ->where('topic_id', $topic->id)
            ->delete();

        return back()->with('success', 'Topic unliked.');
    }
}
