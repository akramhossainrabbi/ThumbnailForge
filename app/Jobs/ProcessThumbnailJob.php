<?php

namespace App\Jobs;
use App\Models\ThumbnailJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class ProcessThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $priority;

    /**
     * Create a new job instance.
     */
    public function __construct(public ThumbnailJob $thumbnailJob)
    {
        $this->priority = $thumbnailJob->request->user->priority;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->thumbnailJob->update([
            'status' => 'processing',
            'attempts' => $this->thumbnailJob->attempts + 1
        ]);

        try {
            $serviceUrl = config('services.thumbnail_processor.url');

            // In tests, allow a default test endpoint so Http::fake() can intercept it.
            if (empty($serviceUrl) && (app()->runningUnitTests() || app()->environment('testing'))) {
                $serviceUrl = 'http://nodejs-service:3000/process';
            }

            if (!empty($serviceUrl)) {
                $response = Http::timeout(30)->post($serviceUrl, [
                    'image_url' => $this->thumbnailJob->image_url,
                    'job_id' => $this->thumbnailJob->id
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    $this->thumbnailJob->update([
                        'status' => 'processed',
                        'thumbnail_url' => $data['thumbnail_url'] ?? 'https://example.com/thumbnails/' . basename($this->thumbnailJob->image_url),
                        'processed_at' => now()
                    ]);
                } else {
                    throw new \Exception('Thumbnail service returned status: ' . $response->status());
                }
            } else {
                // No external service configured — use local simulator
                $processor = app(\App\Services\ThumbnailProcessorService::class);
                $data = $processor->simulateNodeJSService($this->thumbnailJob->image_url);

                $this->thumbnailJob->update([
                    'status' => 'processed',
                    'thumbnail_url' => $data['thumbnail_url'] ?? 'https://example.com/thumbnails/' . basename($this->thumbnailJob->image_url),
                    'processed_at' => now()
                ]);
            }

            // Update parent request counters and possibly mark completed
            try {
                $request = \App\Models\ThumbnailRequest::find($this->thumbnailJob->request_id);
                if ($request) {
                    $request->increment('processed_count');
                    $request->refresh();
                    if (($request->processed_count + $request->failed_count) >= $request->total_images) {
                        $request->update(['status' => 'completed']);
                    }
                }
            } catch (\Throwable $e) {
                // swallow - non-fatal if request counters fail to update
            }
        } catch (RequestException $e) {
            $this->thumbnailJob->update([
                'status' => 'failed',
                'error_message' => 'Network error: ' . $e->getMessage(),
                'processed_at' => now()
            ]);

            // increment failed_count on parent request
            try {
                $request = \App\Models\ThumbnailRequest::find($this->thumbnailJob->request_id);
                if ($request) {
                    $request->increment('failed_count');
                    $request->refresh();
                    if (($request->processed_count + $request->failed_count) >= $request->total_images) {
                        $request->update(['status' => 'completed']);
                    }
                }
            } catch (\Throwable $_) {
                // ignore
            }

            throw $e;
        } catch (\Exception $e) {
            $this->thumbnailJob->update([
                'status' => 'failed',
                'error_message' => 'Processing error: ' . $e->getMessage(),
                'processed_at' => now()
            ]);

            try {
                $request = \App\Models\ThumbnailRequest::find($this->thumbnailJob->request_id);
                if ($request) {
                    $request->increment('failed_count');
                    $request->refresh();
                    if (($request->processed_count + $request->failed_count) >= $request->total_images) {
                        $request->update(['status' => 'completed']);
                    }
                }
            } catch (\Throwable $_) {
                // ignore
            }

            throw $e;
        }
    }

    public function failed(\Throwable $e)
    {
        $this->thumbnailJob->update([
            'status' => 'failed',
            'error_message' => $e->getMessage(),
            'processed_at' => now()
        ]);
    }
}
