<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Topic;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_cannot_post_comments()
    {
        $user = User::factory()->create();

        $topic = Topic::create([
            'user_id' => $user->id,
            'title' => 'Topic',
            'body' => 'Body',
        ]);

        $response = $this->post(route('topics.comments.store', $topic), [
            'body' => 'Hello world',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('comments', 0);
    }

    /** @test */
    public function authenticated_user_can_post_comment_on_topic()
    {
        $user = User::factory()->create();
        $author = User::factory()->create();

        $topic = Topic::create([
            'user_id' => $author->id,
            'title' => 'Topic',
            'body' => 'Body',
        ]);

        $payload = [
            'body' => 'Nice topic!',
        ];

        $response = $this->actingAs($user)->post(
            route('topics.comments.store', $topic),
            $payload
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'topic_id' => $topic->id,
            'body' => 'Nice topic!',
        ]);
    }
}
