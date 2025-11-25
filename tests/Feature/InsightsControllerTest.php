<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Topic;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InsightsControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_are_redirected_from_insights()
    {
        $response = $this->get(route('insights.index'));

        $response->assertRedirect(route('login'));
    }

}
