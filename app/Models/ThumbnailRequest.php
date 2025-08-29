<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThumbnailRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'image_urls', 'total_images', 'status', 
        'processed_count', 'failed_count',
    ];

    protected $casts = [
        'image_urls' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobs()
    {
        return $this->hasMany(ThumbnailJob::class, 'request_id');
    }
}
