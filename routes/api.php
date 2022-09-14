<?php

use App\Http\Controllers\Api\ProcessRequestsController;
use App\Http\Controllers\Api\GenerateReportsController;
use Illuminate\Support\Facades\Route;

Route::get('/generate_reports', [GenerateReportsController::class, 'handle']);
Route::post('/process_requests', [ProcessRequestsController::class, 'handle']);
