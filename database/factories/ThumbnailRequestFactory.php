<?php

namespace Database\Factories;

use App\Models\ThumbnailRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThumbnailRequestFactory extends Factory
{
    protected $model = ThumbnailRequest::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'image_urls' => $this->faker->randomElements([
                'https://example.com/image1.jpg',
                'https://example.com/image2.jpg',
                'https://example.com/image3.jpg',
            ], $this->faker->numberBetween(1, 3)),
            'total_images' => 1,
            'status' => 'pending',
            'processed_count' => 0,
            'failed_count' => 0,
        ];
    }
}
