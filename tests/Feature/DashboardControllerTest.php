<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_are_redirected_from_dashboard()
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function dashboard_lists_all_topics_with_basic_stats()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $first = Topic::create([
            'user_id' => $user->id,
            'title' => 'First topic',
            'body' => 'Body 1',
        ]);

        $second = Topic::create([
            'user_id' => $other->id,
            'title' => 'Second topic',
            'body' => 'Body 2',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('topics', 2)
            ->where('topics.0.id', $first->id)
            ->where('topics.1.id', $second->id)
        );
    }
}
