<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tier',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function thumbnailRequests()
    {
        return $this->hasMany(ThumbnailRequest::class);
    }

    public function getMaxImagesAttribute()
    {
        return match($this->tier) {
            'free' => 50,
            'pro' => 100,
            'enterprise' => 200,
            default => 50
        };
    }

    public function getPriorityAttribute()
    {
        return match($this->tier) {
            'free' => 1,
            'pro' => 2,
            'enterprise' => 3,
            default => 1
        };
    }
}
