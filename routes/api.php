<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\AboutController;
use App\Http\Controllers\Api\V1\AppInformationController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\CorporateController;
use App\Http\Controllers\Api\V1\LocationMapController;
use App\Http\Controllers\Api\V1\WhiteBlowingSystemController;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::group(['prefix' => 'v1', 'middleware' => 'validate_api_key'], function () {
    // Route::get('/help', [HelpController::class, 'index']); //help
    Route::get('location', [LocationController::class, 'location']); //location
    Route::group(['prefix' => 'white-blowing-system'], function () {
        //start route white-blowing-system
        Route::get('/category', [WhiteBlowingSystemController::class, 'category']); //white-blowing-system
        Route::post('/track', [WhiteBlowingSystemController::class, 'track']); //white-blowing-system
        Route::post('/store', [WhiteBlowingSystemController::class, 'store']); //white-blowing-system
        //end route white-blowing-system
    });
    Route::get('/home', [HomeController::class, 'index']); //home
    Route::get('contact', [ContactController::class, 'index']); //contact

    Route::group(['prefix' => 'about'], function () {
        Route::get('history', [AboutController::class, 'history']); //about
        Route::get('achievement', [AboutController::class, 'achievement']); //about achievement
        Route::get('vision-mission', [AboutController::class, 'visionMission']); //about vision-mission
        Route::get('director', [AboutController::class, 'director']); //about director
        Route::get('certificate', [AboutController::class, 'certificate']); //about certificate
    });

    Route::group(['prefix' => 'product'], function () {
        //start route product
        Route::get('industrial-area', [ProductController::class, 'industrialArea']); //product
        Route::get('commercial', [ProductController::class, 'commercial']); //product
        Route::get('list-realestate', [ProductController::class, 'listRealEstate']); //product
        Route::get('real-estate', [ProductController::class, 'realEstate']); //product
        //end route product
    });

    Route::group(['prefix' => 'corporate'], function () {
        //start route corporate
        Route::get('csr', [CorporateController::class, 'csr']); //csr
        Route::get('gcg', [CorporateController::class, 'gcg']); //gcg
        Route::get('risk-management', [CorporateController::class, 'riskManagement']); //risk-management

        Route::post('file-download', [CorporateController::class, 'fileDownload'])->middleware('throttle:etika'); //file-download
    });

    Route::group(['prefix' => 'news'], function () {
        Route::get('news-article', [NewsController::class, 'news']);
        Route::get('news-article/detail', [NewsController::class, 'newsDetail']);
        // Route::get('news-article/{id}', [NewsController::class, 'getNews']);
        Route::get('e-magazine', [NewsController::class, 'eMagazine']);
        Route::get('announcement', [NewsController::class, 'announcement']);
        Route::get('announcement/detail', [NewsController::class, 'announcementDetail']);
        // Route::get('announcement/{id}', [NewsController::class, 'getAnouncement']);
    });

    Route::group(['prefix' => 'location'], function () {
        Route::get('live-map', [LocationMapController::class, 'liveMap']);
        Route::get('live-map/{id}', [LocationMapController::class, 'getLiveMap']);
        Route::get('area-map', [LocationMapController::class, 'areaMap']);
        Route::get('area-map/{id}', [LocationMapController::class, 'getAreaMap']);
    });

    Route::group(['prefix' => 'information'], function () {
        Route::get('privacy-policy', [AppInformationController::class, 'privacyPolicy']);
        Route::get('terms-and-conditions', [AppInformationController::class, 'termsAndConditions']);
        Route::get('disclaimer', [AppInformationController::class, 'disclaimer']);
    });

    Route::group(['prefix' => 'auth'], function () {
        //end route auth
    });

    Route::group(['middleware' => 'auth:api'], function () {
    });
});
