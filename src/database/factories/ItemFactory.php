<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Condition;

class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(), 
            'condition_id' => 1, 
            'name' => $this->faker->word,
            'brand_name' => $this->faker->word,
            'price' => $this->faker->numberBetween(100, 10000),
            'description' => $this->faker->sentence,
            'img_url' => 'http://example.com/dummy.jpg', 
        ];
    }
}