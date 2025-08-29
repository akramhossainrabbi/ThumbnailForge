<?php

namespace App\Services;

use App\Models\ThumbnailRequest;
use App\Models\ThumbnailJob;
use App\Jobs\ProcessThumbnailJob;
use Illuminate\Support\Facades\Validator;

class ThumbnailProcessorService
{
    public function __construct()
    {
        //
    }

    public function processRequest(ThumbnailRequest $request)
    {
        $request->update(['status' => 'processing']);
        
        $validUrls = [];
        $invalidUrls = [];
        
        foreach ($request->image_urls as $imageUrl) {
            $trimmedUrl = trim($imageUrl);
            
            if ($this->isValidUrl($trimmedUrl)) {
                $validUrls[] = $trimmedUrl;
            } else {
                $invalidUrls[] = $trimmedUrl;
                
                ThumbnailJob::create([
                    'request_id' => $request->id,
                    'image_url' => $trimmedUrl,
                    'status' => 'failed',
                    'error_message' => 'Invalid URL format',
                    'processed_at' => now()
                ]);
            }
        }
        
        $request->update([
            'processed_count' => count($invalidUrls), // Mark invalid URLs as "processed" (failed)
            'failed_count' => count($invalidUrls)
        ]);
        
        if (empty($validUrls)) {
            $request->update([
                'status' => 'completed'
            ]);
            return;
        }
        
        $jobs = [];
        foreach ($validUrls as $imageUrl) {
            $job = ThumbnailJob::create([
                'request_id' => $request->id,
                'image_url' => $imageUrl,
                'status' => 'pending'
            ]);
            
            $processJob = new ProcessThumbnailJob($job);
            $processJob->onQueue($this->getQueueName($request->user->tier));
            dispatch($processJob);
        }
    }

    protected function getQueueName(string $tier): string
    {
        return match($tier) {
            'enterprise' => 'high',
            'pro' => 'medium',
            default => 'low'
        };
    }
    
    protected function isValidUrl(string $url): bool
    {
        if (empty($url)) {
            return false;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
            
            if (!in_array($extension, $validExtensions)) {
                return false;
            }
        }
        
        return true;
    }

    public function simulateNodeJSService(string $imageUrl): array
    {
        if (!$this->urlExists($imageUrl)) {
            throw new \Exception('URL does not exist or is unreachable');
        }
        
        sleep(rand(1, 5));

        if (rand(1, 10) === 1) {
            throw new \Exception('Simulated processing failure');
        }

        return [
            'thumbnail_url' => 'https://example.com/thumbnails/' . basename($imageUrl),
            'original_url' => $imageUrl,
            'processed_at' => now()->toISOString()
        ];
    }
    
    protected function urlExists(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $validDomains = [
            'images.unsplash.com',
            'images.pexels.com',
            'picsum.photos',
            'placekitten.com',
            'placehold.co',
            'http.cat'
        ];
        
        foreach ($validDomains as $domain) {
            if (strpos($host, $domain) !== false) {
                return true;
            }
        }

        return rand(1, 10) > 2;
    }
}