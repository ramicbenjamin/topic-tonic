<?php

namespace Database\Factories;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TopicFactory extends Factory
{
    protected $model = Topic::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title'   => $this->faker->sentence(),
            'body'    => $this->faker->paragraph(3),
        ];
    }
}
