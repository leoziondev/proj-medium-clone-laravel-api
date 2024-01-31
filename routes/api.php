<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post("/auth/logout", [AuthController::class, "logout"]);
    Route::apiResource("post", PostController::class)->except("index", "show");
});

// Public routes
Route::apiResource("post", PostController::class)->only(["index", "show"]);

// Auth routes
Route::post("/auth/register", [AuthController::class, "register"]);
Route::post("/auth/login", [AuthController::class, "login"]);
