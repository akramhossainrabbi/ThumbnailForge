@extends('layouts.app')
@php
    use App\Models\ThumbnailJob;
    $user = auth()->user();
    $total = \App\Models\ThumbnailJob::whereHas('request', fn($q) => $q->where('user_id', $user->id))->count();
    $processed = \App\Models\ThumbnailJob::whereHas('request', fn($q) => $q->where('user_id', $user->id))->where('status', 'processed')->count();
    $failed = \App\Models\ThumbnailJob::whereHas('request', fn($q) => $q->where('user_id', $user->id))->where('status', 'failed')->count();
    $pending = \App\Models\ThumbnailJob::whereHas('request', fn($q) => $q->where('user_id', $user->id))->where('status', 'pending')->count();
@endphp

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-2">Thumbnail Processing Stats</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-700">{{ $total }}</div>
                            <div class="text-gray-700">Total</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-green-700">{{ $processed }}</div>
                            <div class="text-gray-700">Processed</div>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-yellow-700">{{ $pending }}</div>
                            <div class="text-gray-700">Pending</div>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-red-700">{{ $failed }}</div>
                            <div class="text-gray-700">Failed</div>
                        </div>
                    </div>
                    <a href="{{ route('thumbnail.create') }}" class="inline-block mt-2 px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">Go to Thumbnail Processing</a>
                </div>
                <div>{{ __("You're logged in!") }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
