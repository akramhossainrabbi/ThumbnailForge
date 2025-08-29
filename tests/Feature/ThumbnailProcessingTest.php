<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\ThumbnailRequest;
use App\Models\ThumbnailJob;
use App\Jobs\ProcessThumbnailJob;

class ThumbnailProcessingTest extends TestCase
{
    public function test_thumbnail_job_processing()
    {
        $url = config('services.thumbnail_processor.url') ?? 'http://nodejs-service:3000/process';
        Http::fake([
            $url => Http::response([
                'thumbnail_url' => 'https://example.com/thumbnails/test.jpg'
            ], 200),
        ]);

        $user = User::factory()->create(['tier' => 'pro']);
        $request = ThumbnailRequest::factory()->create([
            'user_id' => $user->id,
            'image_urls' => ['https://example.com/image.jpg'],
            'total_images' => 1,
        ]);
        $job = ThumbnailJob::create([
            'request_id' => $request->id,
            'image_url' => 'https://example.com/image.jpg',
            'status' => 'pending',
        ]);

        ProcessThumbnailJob::dispatchSync($job);

        $this->assertDatabaseHas('thumbnail_jobs', [
            'id' => $job->id,
            'status' => 'processed',
            'thumbnail_url' => 'https://example.com/thumbnails/test.jpg',
        ]);
    }
}
