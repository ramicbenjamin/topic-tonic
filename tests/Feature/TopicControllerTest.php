<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TopicControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_are_redirected_from_topics_index()
    {
        $response = $this->get(route('topics.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_sees_only_their_own_topics_in_index()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myTopic = Topic::create([
            'user_id' => $user->id,
            'title' => 'My Topic',
            'body' => 'My body',
        ]);

        Topic::create([
            'user_id' => $otherUser->id,
            'title' => 'Other Topic',
            'body' => 'Other body',
        ]);

        $response = $this->actingAs($user)->get(route('topics.index'));

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('topics/Index')
            ->has('topics.data', 1)
            ->where('topics.data.0.id', $myTopic->id)
        );
    }

    /** @test */
    public function user_can_create_a_topic()
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'New Topic Title',
            'body' => 'This is a new topic body.',
        ];

        $response = $this->actingAs($user)->post(route('topics.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('topics', [
            'user_id' => $user->id,
            'title' => 'New Topic Title',
        ]);
    }

    /** @test */
    public function show_displays_topic_with_comments()
    {
        $user = User::factory()->create();

        $topic = Topic::create([
            'user_id' => $user->id,
            'title' => 'Topic with interactions',
            'body' => 'Some body',
        ]);

        $response = $this->actingAs($user)->get(route('topics.show', $topic));

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('topics/Show')
            ->where('topic.id', $topic->id)
            ->has('topic.comments')
        );
    }
}
