<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TopicController extends Controller
{
    public function index(Request $request): Response
    {
        $topics = Topic::query()
            ->where('user_id', auth()->id())
            ->with('user:id,name')
            ->latest()
            ->paginate(10)
            ->through(function (Topic $topic) {
                return [
                    'id'         => $topic->id,
                    'title'      => $topic->title,
                    'body'       => $topic->body,
                    'author'     => $topic->user?->name,
                    'created_at' => $topic->created_at?->toDateTimeString(),
                    'created_human' => $topic->created_at?->diffForHumans(),
                ];
            });

        return Inertia::render('topics/Index', [
            'topics' => $topics
        ]);

    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['nullable', 'string'],
        ]);

        $topic = $request->user()->topics()->create($validated);

        return redirect()
            ->route('topics.show', $topic)
            ->with('success', 'Topic created.');
    }

    public function show(Request $request, Topic $topic): Response
    {
        $user = $request->user();

        $topic->loadCount(['comments', 'likes'])
            ->load(['user', 'comments.user']);

        return Inertia::render('topics/Show', [
            'topic' => [
                'id' => $topic->id,
                'title' => $topic->title,
                'body' => $topic->body,
                'author_name' => $topic->user?->name ?? 'Unknown',
                'created_human' => optional($topic->created_at)->diffForHumans() ?? '',
                'likes_count' => $topic->likes_count,
                'comments_count' => $topic->comments_count,
                'viewer_has_liked' => $user
                    ? $topic->likes()->where('user_id', $user->id)->exists()
                    : false,
                'comments' => $topic->comments
                    ->sortBy('created_at')
                    ->map(function ($comment) use ($user) {
                        return [
                            'id' => $comment->id,
                            'body' => $comment->body,
                            'author_name' => $comment->user?->name ?? 'Unknown',
                            'created_human' => optional($comment->created_at)->diffForHumans() ?? '',
                            'can_delete' => $user && $comment->user_id === $user->id,
                        ];
                    })
                    ->values(),
            ],
        ]);
    }
}
