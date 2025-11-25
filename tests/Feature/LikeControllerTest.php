<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Topic;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_cannot_like_topics()
    {
        $user = User::factory()->create();

        $topic = Topic::create([
            'user_id' => $user->id,
            'title' => 'Topic',
            'body' => 'Body',
        ]);

        $response = $this->post(route('topics.likes.store', $topic));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('likes', 0);
    }

    /** @test */
    public function authenticated_user_can_like_a_topic()
    {
        $user = User::factory()->create();
        $author = User::factory()->create();

        $topic = Topic::create([
            'user_id' => $author->id,
            'title' => 'Topic',
            'body' => 'Body',
        ]);

        $response = $this->actingAs($user)->post(
            route('topics.likes.store', $topic)
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'topic_id' => $topic->id,
        ]);
    }

    /** @test */
    public function authenticated_user_can_unlike_a_topic()
    {
        $user = User::factory()->create();
        $author = User::factory()->create();

        $topic = Topic::create([
            'user_id' => $author->id,
            'title' => 'Topic',
            'body' => 'Body',
        ]);

        // Pre-like
        Like::create([
            'user_id' => $user->id,
            'topic_id' => $topic->id,
        ]);

        $response = $this->actingAs($user)->delete(
            route('topics.likes.destroy', $topic)
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'topic_id' => $topic->id,
        ]);
    }
}
