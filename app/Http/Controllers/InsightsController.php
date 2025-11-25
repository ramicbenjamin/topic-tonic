<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Topic;
use App\Models\Comment;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class InsightsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // --- Base collections -------------------------------------------------

        $topics = Topic::query()
            ->where('user_id', $user->id)
            ->withCount(['comments', 'likes'])
            ->orderByDesc('created_at')
            ->get();

        $totalTopics   = $topics->count();
        $totalComments = Comment::whereHas('topic', fn ($q) => $q->where('user_id', $user->id))->count();
        $totalLikes    = Like::whereHas('topic', fn ($q) => $q->where('user_id', $user->id))->count();

        $avgCommentsPerTopic = $totalTopics > 0 ? round($totalComments / $totalTopics, 1) : 0.0;
        $avgLikesPerTopic    = $totalTopics > 0 ? round($totalLikes / $totalTopics, 1) : 0.0;

        // --- Activity: quick “at a glance” items ------------------------------

        $activity = [
            [
                'label' => 'Total topics',
                'value' => (string) $totalTopics,
            ],
            [
                'label' => 'Total comments',
                'value' => (string) $totalComments,
            ],
            [
                'label' => 'Total likes',
                'value' => (string) $totalLikes,
            ],
            [
                'label' => 'Avg. engagement / topic',
                'value' => sprintf('%s comments • %s likes', $avgCommentsPerTopic, $avgLikesPerTopic),
            ],
        ];

        // --- Topics table data ------------------------------------------------

        $topicSummaries = $topics->map(function (Topic $topic) {
            return [
                'id'              => $topic->id,
                'title'           => $topic->title,
                'comments_count'  => $topic->comments_count,
                'likes_count'     => $topic->likes_count,
                'created_human'   => $topic->created_at->diffForHumans(),
            ];
        });

        // --- Chart data: last 14 days ----------------------------------------

        $days = 14;
        $from = Carbon::now()->subDays($days - 1)->startOfDay();

        $topicsByDate = Topic::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $from)
            ->selectRaw('date(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date'); // ['2025-11-20' => 3, ...]

        $likesByDate = Like::query()
            ->whereHas('topic', fn ($q) => $q->where('user_id', $user->id))
            ->where('created_at', '>=', $from)
            ->selectRaw('date(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $period = CarbonPeriod::create($from, Carbon::now()->startOfDay());

        $perDay = collect($period)->map(function (Carbon $date) use ($topicsByDate, $likesByDate) {
            $key = $date->toDateString();

            return [
                'date'         => $key,
                'topics_count' => (int) ($topicsByDate[$key] ?? 0),
                'likes_count'  => (int) ($likesByDate[$key] ?? 0),
            ];
        })->values();

        return Inertia::render('insights/Index', [
            'stats' => [
                'totalTopics'         => $totalTopics,
                'totalComments'       => $totalComments,
                'totalLikes'          => $totalLikes,
                'avgCommentsPerTopic' => $avgCommentsPerTopic,
                'avgLikesPerTopic'    => $avgLikesPerTopic,
            ],
            'activity' => $activity,
            'topics'   => $topicSummaries,
            'charts'   => [
                'per_day' => $perDay,
            ],
        ]);
    }
}
