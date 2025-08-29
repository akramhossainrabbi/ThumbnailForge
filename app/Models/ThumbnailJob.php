<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThumbnailJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id', 'image_url', 'status', 'thumbnail_url',
        'error_message', 'attempts', 'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'updated' => \App\Events\ThumbnailJobUpdated::class,
    ];

    public function request()
    {
        return $this->belongsTo(ThumbnailRequest::class);
    }
}
