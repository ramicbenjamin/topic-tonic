<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InsightsController;
use App\Http\Controllers\TopicLikeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\TopicController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');
    Route::get('/topics', [TopicController::class, 'index'])->name('topics.index');
    Route::post('/topics', [TopicController::class, 'store'])->name('topics.store');
    Route::get('/topics/{topic}', [TopicController::class, 'show'])->name('topics.show');
    Route::post('/topics/{topic}/comments', [CommentController::class, 'store'])
        ->name('topics.comments.store');

    Route::delete('/topics/{topic}/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('topics.comments.destroy');

    // Likes
    Route::post('/topics/{topic}/likes', [TopicLikeController::class, 'store'])
        ->name('topics.likes.store');

    Route::delete('/topics/{topic}/likes', [TopicLikeController::class, 'destroy'])
        ->name('topics.likes.destroy');

    Route::get('/insights', [InsightsController::class, 'index'])
        ->name('insights.index');
});


require __DIR__.'/settings.php';
