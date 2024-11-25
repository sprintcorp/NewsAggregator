<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'news_id' => $this->faker->sentence,
            'title' => $this->faker->sentence,
            'category' => $this->faker->randomElement(['Technology', 'Health', 'Sports']),
            'source' => $this->faker->randomElement(['The Guardian', 'The Newyork Times']),
            'author' => $this->faker->name,
            'body' => $this->faker->paragraph,
            'published_date' => $this->faker->dateTime,
        ];
    }
}
