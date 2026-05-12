<?php

use Maatwebsite\Excel\Row;
use App\Models\Information;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BillController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\SaldoController;
use App\Http\Controllers\Api\V1\SavingController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\TahfidzController;
use App\Http\Controllers\Api\V1\ScheduleController;
use App\Http\Controllers\Api\V1\StudyGradeController;
use App\Http\Controllers\Api\V1\InformationController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\ForgotPasswordController;
use App\Http\Controllers\Api\V1\ApplicationMenuController;
use App\Http\Controllers\Api\V1\StudentAchievementController;
use App\Http\Controllers\Api\V1\StudentCounselingScoreController;


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
        Route::post('update-fcm-token', [AuthController::class, 'updateFcm']);
        Route::get('home', [HomeController::class, 'index']);
        Route::get('information', [HomeController::class, 'information']);
        Route::get('list-student', [HomeController::class, 'listStudent']);

        // start api bill
        Route::group(['prefix' => 'bill'], function () {
            Route::get('/', [BillController::class, 'index']);
            Route::get('/history', [BillController::class, 'history']);
            Route::get('/{id}', [BillController::class, 'show']);
        });

        // start api transaction
        Route::group(['prefix' => 'transaction'], function () {
            Route::get('payment-method', [TransactionController::class, 'listPaymentMethod']);
            Route::post('/pay', [TransactionController::class, 'pay']);
            Route::post('/upload-proof', [TransactionController::class, 'uploadProof']);
            Route::get('/proof/{id}', [TransactionController::class, 'proof']);
            Route::post('/reupload-proof/{id}', [TransactionController::class, 'reuploadProof']);
            Route::get('/{id}', [TransactionController::class, 'show']);
        });

        // start saldo
        Route::group(['prefix' => 'saldo'], function () {
            Route::get('/', [SaldoController::class, 'index']);
            Route::get('/{id}', [SaldoController::class, 'show']);
            Route::post('/block', [SaldoController::class, 'block']);
            Route::post('/topup', [SaldoController::class, 'topup']);
            Route::post('/sett-limit', [SaldoController::class, 'settLimit']);
        });

        // start saving
        Route::group(['prefix' => 'saving'], function () {
            Route::get('/', [SavingController::class, 'index']);
            Route::post('/topup', [SavingController::class, 'topup']);
            Route::get('/{id}', [SavingController::class, 'show']);
        });

        // start tahfidz
        Route::group(['prefix' => 'tahfidz'], function () {
            Route::get('/', [TahfidzController::class, 'index']);
            Route::get('/{id}', [TahfidzController::class, 'show']);
        });
        // start student achievement
        Route::group(['prefix' => 'student-achievement'], function () {
            Route::get('/', [StudentAchievementController::class, 'index']);
        });

        // start student counseling score
        Route::group(['prefix' => 'student-counseling-score'], function () {
            Route::get('/', [StudentCounselingScoreController::class, 'index']);
        });

        // start schedule
        Route::group(['prefix' => 'schedule'], function () {
            Route::get('/', [ScheduleController::class, 'index']);
        });

        // start study-grade
        Route::group(['prefix' => 'study-grade'], function () {
            Route::get('/', [StudyGradeController::class, 'index']);
            Route::get('/list-classroom', [StudyGradeController::class, 'listClassroom']);
            Route::get('list-semester', [StudyGradeController::class, 'listSemester']);
        });

        // start profile
        Route::group(['prefix' => 'profile'], function () {
            Route::get('/', [ProfileController::class, 'index']);
            Route::post('update-avatar', [ProfileController::class, 'updateAvatar']);
            Route::post('update-profile', [ProfileController::class, 'updateProfile']);
            Route::post('change-password', [ProfileController::class, 'changePassword']);
        });
    });
    Route::get('app-information', [InformationController::class, 'index']);
    Route::group(['prefix' => 'information'], function () {
        Route::get('/help', [InformationController::class, 'help']);
        Route::get('app-setting', [InformationController::class, 'appSetting']);
    });

    // start application menu
    Route::group(['prefix' => 'application-menu'], function () {
        Route::get('/', [ApplicationMenuController::class, 'index']);
    });

    // start forgot password
    Route::group(
        ['prefix' => 'forgot-password'],
        function () {
            Route::get('request-token', [ForgotPasswordController::class, 'requestToken']);
            Route::post('match-token', [ForgotPasswordController::class, 'matchToken']);
            Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);
        }
    );
});



Route::group(['middleware' => 'xendit'], function () {
    Route::post('callback-xendit', [TransactionController::class, 'callbackXendit']);
});

Route::prefix('wali')->middleware(['web'])->group(function () {
    Route::post('login', [App\Http\Controllers\Api\Wali\AuthController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\Api\Wali\AuthController::class, 'logout']);

    Route::middleware(['auth:wali'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\Api\Wali\DashboardController::class, 'index']);
    Route::get('informations', [App\Http\Controllers\Api\Wali\InformationController::class, 'index']);
    Route::get('students', [App\Http\Controllers\Api\Wali\StudentController::class, 'index']);
    Route::get('active-student', [App\Http\Controllers\Api\Wali\StudentController::class, 'active']);
    Route::get('saldo-histories', [App\Http\Controllers\Api\Wali\SaldoHistoryController::class, 'index']);
    Route::get('saving-histories', [App\Http\Controllers\Api\Wali\SavingHistoryController::class, 'index']);
    Route::get('bills', [App\Http\Controllers\Api\Wali\BillController::class, 'index']);
    Route::get('pos-transactions', [App\Http\Controllers\Api\Wali\PosTransactionController::class, 'index']);
    Route::post('topup', [App\Http\Controllers\Api\Wali\TopupController::class, 'store']);
    Route::post('checkout', [App\Http\Controllers\Api\Wali\CheckoutController::class, 'store']);
    Route::get('payment/{id}', [App\Http\Controllers\Api\Wali\PaymentController::class, 'show']);
    Route::post('payment/{id}/upload-proof', [App\Http\Controllers\Api\Wali\PaymentProofController::class, 'store']);
    Route::get('limit', [App\Http\Controllers\Api\Wali\LimitController::class, 'show']);
    Route::put('limit', [App\Http\Controllers\Api\Wali\LimitController::class, 'update']);
    Route::get('profile', [App\Http\Controllers\Api\Wali\ProfileController::class, 'show']);
    Route::put('profile', [App\Http\Controllers\Api\Wali\ProfileController::class, 'update']);
    Route::put('password', [App\Http\Controllers\Api\Wali\PasswordController::class, 'update']);
    });
});
