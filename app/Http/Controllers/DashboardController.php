<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the Topic Tonic dashboard with all topics from everyone.
     */
    public function __invoke(): Response
    {
        $topics = Topic::with('user')
            ->withCount(['comments', 'likes'])
            ->latest()
            ->get()
            ->map(function (Topic $topic) {
                return [
                    'id' => $topic->id,
                    'title' => $topic->title,
                    'body' => $topic->body,
                    'author' => $topic->user?->name,
                    'created_human' => $topic->created_at?->diffForHumans() ?? '',
                    'comments_count' => $topic->comments_count,
                    'likes_count' => $topic->likes_count,
                ];
            });

        return Inertia::render('Dashboard', [
            'topics' => $topics,
        ]);
    }
}
