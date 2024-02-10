<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\TransactionController;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group(['prefix' => 'v1', 'middleware' => 'validate_api_key'], function () {
    // Route::get('/help', [HelpController::class, 'index']); //help

    Route::group(['prefix' => 'auth'], function () {
        //end route auth
    });

    Route::group(['middleware' => 'auth:api'], function () {
    });
});

Route::group(['middleware' => 'xendit'], function () {
    Route::post('callback-xendit', [TransactionController::class, 'callbackXendit']);
});
