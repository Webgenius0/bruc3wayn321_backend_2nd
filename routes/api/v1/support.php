<?php

use App\Http\Controllers\API\SupportIssueController;
use Illuminate\Support\Facades\Route;

Route::post('support/issues', [SupportIssueController::class, 'store']);
