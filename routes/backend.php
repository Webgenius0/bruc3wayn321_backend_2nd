<?php

// use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Web\Backend\DashboardController;
use App\Http\Controllers\Web\Backend\DynamicPageController;
use App\Http\Controllers\Web\Backend\FaqController;
use App\Http\Controllers\Web\Backend\LogController;
use App\Http\Controllers\Web\Backend\MessageController;
use App\Http\Controllers\Web\Backend\Post\PostController;
use App\Http\Controllers\Web\Backend\ProfileController;
use App\Http\Controllers\Web\Backend\QueueController;
use App\Http\Controllers\Web\Backend\SystemSettingsController;
use App\Http\Controllers\Web\Backend\UserController;
use App\Http\Controllers\Web\Backend\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');

    // User APIs controller 
    Route::get('/users', [UserController::class, 'index'])->name('admin.user.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/user/edit/{id}', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::get('/user/show/{id}', [UserController::class, 'show'])->name('admin.users.show');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');


    // Post CRUD routes
    Route::get('/posts', [PostController::class, 'index'])->name('admin.posts.index');
    Route::get('/post/show/{post:slug}', [PostController::class, 'show'])->name('admin.posts.show');
    Route::get('/post/edit/{post:slug}', [PostController::class, 'edit'])->name('admin.posts.edit');

    Route::get('/posts/create', [PostController::class, 'create'])->name('admin.posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('admin.posts.store');
    // Route::get('/posts/{post}', [PostController::class, 'show'])->name('admin.posts.show');
    // Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('admin.posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('admin.posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('admin.posts.destroy');
    Route::get('users/{user}/sessions', [UserController::class, 'sessions'])->name('admin.users.sessions');


    // System setting here 
    Route::get('/settings/system', [SystemSettingsController::class, 'index'])->name('admin.settings.system.index');
    Route::post('/settings/system/update', [SystemSettingsController::class, 'update'])->name('admin.settings.system.update');


    Route::get('/settings/smtp', [SystemSettingsController::class, 'smtp'])->name('admin.settings.smtp');
    Route::post('/settings/smtp/update', [SystemSettingsController::class, 'updateSmtp'])->name('admin.settings.smtp.update');
    Route::get('/settings/stripe', [SystemSettingsController::class, 'stripe'])->name('admin.settings.stripe');
    Route::post('/settings/stripe/update', [SystemSettingsController::class, 'updateStripe'])->name('admin.settings.stripe.update');


    //profile routes and controller
    Route::get('/profile', [ProfileController::class, 'index'])->name('admin.profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password.update');

    //Dynamic Pages routes and controller
    Route::get('/page/{slug}', [DynamicPageController::class, 'dynamicPage'])->name('admin.page.index');
    Route::post('/page/update/{slug}', [DynamicPageController::class, 'updatePage'])->name('admin.page.update');

    //profile routes and controller
    Route::get('/faq/list', [FaqController::class, 'index'])->name('admin.faq.index');
    Route::get('/faq/create', [FaqController::class, 'create'])->name('admin.faq.create');
    Route::post('/faq/store', [FaqController::class, 'store'])->name('admin.faq.store');
    Route::get('/faq/edit/{id}', [FaqController::class, 'edit'])->name('admin.faq.edit');
    Route::post('/faq/update/{id}', [FaqController::class, 'update'])->name('admin.faq.update');
    Route::delete('/faq/delete/{id}', [FaqController::class, 'destroy'])->name('admin.faq.delete');

    //Logs controller
    Route::get('/logs', [LogController::class, 'index'])->name('admin.log.index');
    Route::delete('/logs', [LogController::class, 'clear'])->name('admin.log.clear');

    Route::get('/queues', [QueueController::class, 'index'])->name('admin.queues.index');
    Route::get('/failed-jobs', [QueueController::class, 'failed'])->name('admin.failed-jobs.index');
    Route::post('/failed-jobs/{id}/retry', [QueueController::class, 'retry'])->name('admin.failed-jobs.retry');
    Route::delete('/failed-jobs/{id}', [QueueController::class, 'destroy'])->name('admin.failed-jobs.destroy');


    Route::get('/messages', [MessageController::class, 'index'])->name('admin.message.index');
});
