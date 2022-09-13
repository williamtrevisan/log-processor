<?php

use App\Http\Controllers\Api\ProcessRequestsController;
use Illuminate\Support\Facades\Route;

Route::post('/process_requests', [ProcessRequestsController::class, 'handle']);
