<?php

use App\Http\Controllers\API\Post\PostController;
use Illuminate\Support\Facades\Route;

// Custom public endpoints (must be declared before resource routes to avoid slug conflict)
Route::get('posts/trending', [PostController::class, 'trending']);
Route::get('posts/latest',   [PostController::class, 'latest']);
Route::post('posts/{post:slug}/draft',    [PostController::class, 'draft']);
Route::post('posts/{post:slug}/publish', [PostController::class, 'publish']);

// Exclude custom endpoint names from the {post} route parameter
Route::pattern('post', '^(?!latest$|trending$|draft$|publish$).*$');

// Explicit store and update routes
Route::post('posts', [PostController::class, 'store']);
Route::put('posts/{post:slug}', [PostController::class, 'update']);

// Public API routes (slug binding) - exclude store and update as they are defined above
Route::apiResource('posts', PostController::class)->parameters([
    'posts' => 'post:slug',
])->except(['store', 'update']);

// Admin panel routes (protected)
// Route::middleware(['auth:sanctum', 'can:admin'])
//     ->prefix('admin')
//     ->group(function () {
//         Route::get('posts',            [PostController::class, 'adminIndex'])->name('admin.posts.index');
//         Route::get('posts/create',    [PostController::class, 'adminCreate'])->name('admin.posts.create');
//         Route::post('posts',          [PostController::class, 'adminStore'])->name('admin.posts.store');
//         Route::get('posts/{post}',    [PostController::class, 'adminShow'])->name('admin.posts.show');
//         Route::get('posts/{post}/edit',[PostController::class, 'adminEdit'])->name('admin.posts.edit');
//         Route::put('posts/{post}',     [PostController::class, 'adminUpdate'])->name('admin.posts.update');
//         Route::delete('posts/{post}',  [PostController::class, 'adminDestroy'])->name('admin.posts.destroy');
//     });
