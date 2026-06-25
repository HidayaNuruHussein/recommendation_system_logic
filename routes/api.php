<?php

use App\Http\Controllers\Api\RecommendationController;
use Illuminate\Support\Facades\Route;

// PHP-based recommendations (local Apriori algorithm)
Route::get('/recommendations/{productId}', RecommendationController::class);
