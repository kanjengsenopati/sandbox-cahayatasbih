<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\Select2Controller;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TranslateController;
use App\Http\Controllers\Admin\DisclaimerController;
use App\Http\Controllers\Admin\HomeSliderController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\PrivacyPolicyController;
use App\Http\Controllers\Admin\TermConditionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('admins.auth.login');
});
//auth
Route::get('/', [AuthController::class, 'index']);
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('translate', [TranslateController::class, 'index'])->name('translate');
Route::post('translate_post', [TranslateController::class, 'translatePost'])->name('translate_post');
Route::post('/upload-image', [ImageUploadController::class, 'upload'])->name('upload.image');
//end auth

// start status
Route::post('/status/{id}', [AcademicYearController::class, 'status'])->name('academic-year.status');
// end status
Route::group(['middleware' => ['auth']], function () {
    Route::resource('permission', PermissionController::class, ['except' => ['show']]);
    Route::resource('role', RoleController::class);
    Route::resource('admin', AdminController::class);
    Route::resource('user', UserController::class);
    Route::resource('school', SchoolController::class);
    Route::resource('classroom', ClassroomController::class, ['except' => ['index', 'show']]);
    Route::resource('academic-year', AcademicYearController::class, ['except' => ['show']]);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('student', StudentController::class);
    Route::get('select2', [Select2Controller::class, 'index'])->name('select2');
    // home
    Route::resource('contact', ContactController::class, ['only' => ['index', 'store']])->names('contact');
    Route::group(['prefix' => 'information'], function () {
        Route::resource(
            'privacy-policy',
            PrivacyPolicyController::class,
            ['only' => ['index', 'store']]
        )->names('privacy-policy');
        Route::resource(
            'term-condition',
            TermConditionController::class,
            ['only' => ['index', 'store']]
        )->names('term-condition');
        Route::resource('disclaimer', DisclaimerController::class, ['only' => ['index', 'store']])->names('disclaimer');
    });
});
