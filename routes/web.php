<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    // Posts
    Route::resource('posts', PostController::class)->only(['create', 'store']);
    Route::resource('posts.comments', CommentController::class)->shallow()->only(['store', 'update', 'destroy']);

    // Likes
    Route::post('/likes/{type}/{id}', [LikeController::class, 'store'])->name('likes.store');
    Route::delete('/likes/{type}/{id}', [LikeController::class, 'destroy'])->name('likes.destroy');
});

Route::get('posts/{post}/{slug?}', [PostController::class, 'show'])
    ->whereNumber('post')
    ->name('posts.show');

Route::get('posts/{topic?}', [PostController::class, 'index'])
    ->name('posts.index');