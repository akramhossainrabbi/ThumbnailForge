<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('thumbnail-requests.{id}', function ($user, $id) {
    return (int) $user->id === (int) \App\Models\ThumbnailRequest::findOrFail($id)->user_id;
});
