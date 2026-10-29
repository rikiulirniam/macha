<?php

use App\Http\Controllers\FeedController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReadingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Macha Web Routes
|--------------------------------------------------------------------------
*/

// PWA Manifest, Service Worker & Offline fallback
Route::get('/manifest.json', function () {
    return response(file_get_contents(public_path('manifest.json')), 200, [
        'Content-Type' => 'application/manifest+json; charset=utf-8',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->name('pwa.manifest');

Route::get('/sw.js', function () {
    return response(file_get_contents(public_path('sw.js')), 200, [
        'Content-Type' => 'application/javascript; charset=utf-8',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Service-Worker-Allowed' => '/',
    ]);
})->name('pwa.sw');

Route::get('/offline.html', function () {
    return response(file_get_contents(public_path('offline.html')), 200, [
        'Content-Type' => 'text/html; charset=utf-8',
    ]);
})->name('pwa.offline');

// Public feed & leaderboard
Route::get('/', [FeedController::class, 'index'])->name('home');
Route::get('/peringkat', [LeaderboardController::class, 'index'])->name('leaderboard');

// Public shared reading
Route::get('/r/{slug}', [ReadingController::class, 'showPublic'])->name('readings.public');

// Public user profile
Route::get('/u/{username}', [ProfileController::class, 'showUser'])->name('profile.user');

// Reading interactions (Progress, Complete, Quiz, Like, Comments)
Route::post('/readings/{reading}/progress', [ReadingController::class, 'recordProgress'])->name('readings.progress');
Route::post('/readings/{reading}/complete', [ReadingController::class, 'complete'])->name('readings.complete');
Route::post('/readings/{reading}/quiz', [ReadingController::class, 'quiz'])->name('readings.quiz');
Route::post('/readings/{reading}/like', [ReadingController::class, 'toggleLike'])->name('readings.like');
Route::get('/readings/{reading}/comments', [ReadingController::class, 'comments'])->name('readings.comments');
Route::post('/readings/{reading}/comments', [ReadingController::class, 'storeComment'])->name('readings.comments.store');
Route::delete('/comments/{comment}', [ReadingController::class, 'deleteComment'])->middleware('auth')->name('comments.destroy');

// Authenticated Onboarding
Route::middleware('auth')->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');

    // Creator routes
    Route::get('/buat', [ReadingController::class, 'create'])->name('readings.create');
    Route::post('/readings', [ReadingController::class, 'store'])->name('readings.store');
    Route::get('/bacaan-saya', [ReadingController::class, 'myReadings'])->name('readings.my');
    Route::get('/readings/{reading}/edit', [ReadingController::class, 'edit'])->name('readings.edit');
    Route::put('/readings/{reading}', [ReadingController::class, 'update'])->name('readings.update');
    Route::delete('/readings/{reading}', [ReadingController::class, 'destroy'])->name('readings.destroy');

    // User profile
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware('auth')->name('dashboard');

require __DIR__.'/auth.php';
