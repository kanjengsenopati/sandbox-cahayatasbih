<?php

use Maatwebsite\Excel\Row;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BillController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\StudentAchievementController;
use App\Http\Controllers\Api\V1\TahfidzController;
use App\Http\Controllers\Api\V1\TransactionController;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group(['prefix' => 'v1', 'middleware' => 'validate_api_key'], function () {
    // Route::get('/help', [HelpController::class, 'index']); //help

    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'phoneLogin']);
        Route::post('logout', [AuthController::class, 'logout']);
    });


    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('home', [HomeController::class, 'index']);
        Route::get('information', [HomeController::class, 'information']);
        Route::get('list-student', [HomeController::class, 'listStudent']);

        // start api bill
        Route::group(['prefix' => 'bill'], function () {
            Route::get('/', [BillController::class, 'index']);
            Route::get('/{id}', [BillController::class, 'show']);
        });

        // start tahfidz
        Route::group(['prefix' => 'tahfidz'], function () {
            Route::get('/', [TahfidzController::class, 'index']);
            Route::get('/{id}', [TahfidzController::class, 'show']);
        });
        // start student achievement
        Route::group(['prefix' => 'student-achievement'], function () {
            Route::get('/', [StudentAchievementController::class, 'index']);
            Route::get('/{id}', [StudentAchievementController::class, 'show']);
        });
    });
});

Route::group(['middleware' => 'xendit'], function () {
    Route::post('callback-xendit', [TransactionController::class, 'callbackXendit']);
});
