<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ThumbnailJob;

class ThumbnailJobUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $thumbnailJob;

    /**
     * Create a new event instance.
     */
    public function __construct(ThumbnailJob $thumbnailJob)
    {
        $this->thumbnailJob = $thumbnailJob;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('thumbnail-requests.' . $this->thumbnailJob->request_id),
        ];
    }

    /**
     * Set a short event name so Echo.listen('ThumbnailJobUpdated') matches.
     */
    public function broadcastAs(): string
    {
        return 'ThumbnailJobUpdated';
    }

    public function broadcastWith()
    {
        $job = $this->thumbnailJob->toArray();

        if ($this->thumbnailJob->processed_at) {
            $job['processed_at'] = $this->thumbnailJob->processed_at instanceof \Illuminate\Support\Carbon
                ? $this->thumbnailJob->processed_at->format('Y-m-d H:i:s')
                : date('Y-m-d H:i:s', strtotime($this->thumbnailJob->processed_at));
        } else {
            $job['processed_at'] = null;
        }

        return [
            'job' => $job
        ];
    }
}
