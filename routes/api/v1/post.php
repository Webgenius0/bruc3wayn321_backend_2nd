<?php

use App\Http\Controllers\API\Post\LikeCommentApiController;
use App\Http\Controllers\API\Post\PostController;
use Illuminate\Support\Facades\Route;

Route::controller(PostController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('posts/trending', 'trending');
    Route::get('posts/latest',   'latest');
    Route::get('posts/followers', 'followersPosts');
    Route::get('posts/user/{id}', 'userPosts');
    Route::post('posts/{post:slug}/draft',    'draft');
    Route::post('posts/{post:slug}/publish', 'publish');
    Route::post('posts', 'store');
    Route::get('post/details/{post:slug}', 'show');
    Route::put('post/update/{post:slug}', 'update');
    Route::post('post/{post:slug}/visibility', 'postVisibility');
    Route::get('post/my-post', 'myPosts');
});

Route::controller(LikeCommentApiController::class)->middleware('auth:sanctum')->group(function () {
    Route::post('posts/{post:slug}/like', 'toggleLike');
    Route::post('posts/{post:slug}/comment', 'comment');
    Route::get('posts/{post:slug}/comments', 'commentList');
    Route::post('posts/{post:slug}/view', 'view');
});
