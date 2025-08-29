<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThumbnailController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


Route::middleware(['auth'])->group(function () {
    Route::get('/thumbnail', [ThumbnailController::class, 'create'])->name('thumbnail.create');
    Route::post('/thumbnail', [ThumbnailController::class, 'store'])->name('thumbnail.store');
    Route::get('/thumbnail/{id}', [ThumbnailController::class, 'show'])->name('thumbnail.show');
    Route::get('/thumbnail/{requestId}/jobs', [ThumbnailController::class, 'getJobs'])->name('thumbnail.jobs');
    Route::get('/requests', [ThumbnailController::class, 'index'])->name('thumbnail.index');
});