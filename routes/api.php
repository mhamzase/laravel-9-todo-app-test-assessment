<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TodoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {

    // verify user account
    Route::post('/verify-account', [AuthController::class, 'verifyAccount']);

    Route::middleware(['verified.user'])->group(function () {
        // logout user
        Route::post('/logout', [AuthController::class, 'logout']);

        // todos resource
        Route::resource('todos', TodoController::class);

        // search todos by query
        Route::get('/search/{query?}', [TodoController::class, 'search']);

        // get logged in user
        Route::get('/user', function () {
            return response()->json([
                'user' => new \App\Http\Resources\UserResourse(auth()->user()),
                'status' => 'success',
            ]);
        });
    });
});
