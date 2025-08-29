<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\ThumbnailProcessorService;

class TestThumbnailProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:thumbnails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test thumbnail processing with different user tiers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();
        $processor = app(ThumbnailProcessorService::class);

        foreach ($users as $user) {
            $this->info("Testing {$user->tier} user: {$user->name}");
            $urls = array_fill(0, min(10, $user->max_images), 'https://example.com/image.jpg');
            $request = $user->thumbnailRequests()->create([
                'image_urls' => $urls,
                'total_images' => count($urls),
                'status' => 'pending'
            ]);
            $processor->processRequest($request);
            $this->info("Created request #{$request->id} with " . count($urls) . " images");
        }
    }
}
