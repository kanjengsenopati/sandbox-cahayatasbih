<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Admin\HelpController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\StudyController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\Select2Controller;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TahfidzController;
use App\Http\Controllers\Admin\BillItemController;
use App\Http\Controllers\Admin\BillTypeController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderItemController;
use App\Http\Controllers\Admin\TranslateController;
use App\Http\Controllers\Admin\DisclaimerController;
use App\Http\Controllers\Admin\HomeSliderController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\StudyGradeController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Admin\InformationController;
use App\Http\Controllers\Admin\PaymentRateController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\CategoryItemController;
use App\Http\Controllers\Admin\SaldoHistoryController;
use App\Http\Controllers\Admin\StockHistoryController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PrivacyPolicyController;
use App\Http\Controllers\Admin\SavingHistoryController;
use App\Http\Controllers\Admin\TermConditionController;
use App\Http\Controllers\Admin\AppInformationController;
use App\Http\Controllers\Admin\ApplicationMenuController;
use App\Http\Controllers\Admin\OrderItemHistoryController;
use App\Http\Controllers\Admin\ApplicationSettingController;
use App\Http\Controllers\Admin\StudentAchievementController;
use App\Http\Controllers\Admin\InformationCategoryController;
use App\Http\Controllers\Admin\StudentCounselingScoreController;

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
    Route::get('student/generate-student-card/{id}', [StudentController::class, 'generateStudentCard'])
        ->name('student.generate-student-card');
    Route::get('bill/get-bill-data', [BillController::class, 'getBillData'])->name('bill.get-bill-data');
    Route::get('bill/summary-bill', [BillController::class, 'summaryBill'])->name('bill.summary-bill');
    Route::get('payment-rate/get-classroom', [PaymentRateController::class, 'getClassroom'])
        ->name('payment-rate.get-classroom');
    Route::get('select2', [Select2Controller::class, 'index'])->name('select2');
    Route::resource('permission', PermissionController::class, ['except' => ['show']]);
    Route::resource('role', RoleController::class);
    Route::resource('admin', AdminController::class);
    Route::resource('user', UserController::class);
    Route::resource('school', SchoolController::class);
    Route::resource('classroom', ClassroomController::class, ['except' => ['index', 'show']]);
    Route::resource('academic-year', AcademicYearController::class, ['except' => ['show']]);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('student', StudentController::class);
    Route::resource('tahfidz', TahfidzController::class);
    Route::resource('category-item', CategoryItemController::class);
    Route::resource('item', ItemController::class);
    Route::resource('stock-history', StockHistoryController::class);

    // Start POS
    Route::get('pos/dashboard', [OrderItemController::class, 'dashboard'])->name('pos.dashboard');
    Route::get('order-item/get-total-price', [OrderItemController::class, 'getTotalPrice'])
        ->name('order-item.get-total-price');
    Route::post('order-item/search-student', [OrderItemController::class, 'getStudentByBarcode'])
        ->name('order-item.search-student');
    Route::get('order-item/get-cart', [OrderItemController::class, 'getCartData'])->name('order-item.get-cart');
    Route::post('order-item/add-to-cart', [OrderItemController::class, 'addItemToCart'])->name('order-item.add-to-cart');
    Route::post('order-item/update-cart-quantity', [OrderItemController::class, 'updateCartQuantity'])->name('order-item.update-cart-quantity');
    Route::post('order-item/delete-from-cart', [OrderItemController::class, 'deleteCart'])
        ->name('order-item.delete-from-cart');
    Route::post('item/search-item', [ItemController::class, 'searchItem'])->name('item.search-item');
    Route::post('order-item/delete-all-cart', [OrderItemController::class, 'deleteAllCart'])
        ->name('order-item.delete-all-cart');
    Route::resource('order-item', OrderItemController::class);
    Route::resource('order-item-history', OrderItemHistoryController::class);

    Route::resource('bill-item', BillItemController::class);
    Route::resource('bill-type', BillTypeController::class);
    Route::resource('payment-rate', PaymentRateController::class);
    Route::resource('bill', BillController::class);
    Route::resource('payment-method', PaymentMethodController::class);
    Route::resource('transaction', TransactionController::class);

    // information
    Route::resource('information-category', InformationCategoryController::class);
    Route::resource('information', InformationController::class);

    // student achievement
    Route::get('student-achievement/get-classroom', [StudentAchievementController::class, 'getClassroom'])
        ->name('student-achievement.get-classroom');
    Route::resource('student-achievement', StudentAchievementController::class);

    // student counseling score
    Route::resource('student-counseling-score', StudentCounselingScoreController::class);
    // home
    Route::resource('contact', ContactController::class, ['only' => ['index', 'store']])->names('contact');
    Route::resource('application-setting', ApplicationSettingController::class, ['only' => ['index', 'store']])->names('application-setting');

    // start saldo history
    Route::resource('saldo-history', SaldoHistoryController::class);
    Route::resource('saving-history', SavingHistoryController::class);

    // start schedule
    Route::resource('schedule', ScheduleController::class);

    Route::resource('help', HelpController::class);
    Route::resource('app-information', AppInformationController::class, ['only' => ['index', 'store']])->names('app-information');

    // start study
    Route::resource('study', StudyController::class);
    Route::resource('semester', SemesterController::class);
    Route::resource('study-grade', StudyGradeController::class);
    Route::post('application-menu/status/{id}', [ApplicationMenuController::class, 'status'])->name('application-menu.status');
    Route::resource('application-menu', ApplicationMenuController::class);

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
    });
});
Route::get('transaction/invoice/{id}', [TransactionController::class, 'invoice'])->name('transaction.invoice');
