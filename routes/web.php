<?php

use Maatwebsite\Excel\Row;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Admin\HelpController;
use App\Http\Controllers\Admin\OfficerController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\PpdbController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\StudyController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\Select2Controller;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TahfidzController;
use App\Http\Controllers\User\CtMobileAuthController;
use App\Http\Controllers\User\WaliPpdbController;
use App\Http\Controllers\Admin\BillItemController;
use App\Http\Controllers\Admin\BillTypeController;
use App\Http\Controllers\Admin\CashFlowController;
use App\Http\Controllers\Admin\PpdbTypeController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderItemController;
use App\Http\Controllers\Admin\PpdbTrackController;
use App\Http\Controllers\Admin\PpdbWavesController;
use App\Http\Controllers\Admin\ReportPosController;
use App\Http\Controllers\Admin\SaldoBankController;
use App\Http\Controllers\Admin\TranslateController;
use App\Http\Controllers\Admin\DisclaimerController;
use App\Http\Controllers\Admin\HomeSliderController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportBillController;
use App\Http\Controllers\Admin\ReportUserController;
use App\Http\Controllers\Admin\SavingBankController;
use App\Http\Controllers\Admin\StudyGradeController;
use App\Http\Controllers\User\PpdbHistoryController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Admin\InformationController;
use App\Http\Controllers\Admin\PaymentRateController;
use App\Http\Controllers\Admin\ReportSaldoController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\CategoryItemController;
use App\Http\Controllers\Admin\ReportAppFeeController;
use App\Http\Controllers\Admin\SaldoHistoryController;
use App\Http\Controllers\Admin\StockHistoryController;
use App\Http\Controllers\User\WaliDashboardController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PrivacyPolicyController;
use App\Http\Controllers\Admin\ReportStudentController;
use App\Http\Controllers\Admin\ReportTahfidzController;
use App\Http\Controllers\Admin\SavingHistoryController;
use App\Http\Controllers\Admin\TermConditionController;
use App\Http\Controllers\Admin\AppInformationController;
use App\Http\Controllers\Admin\GradePromotionController;
use App\Http\Controllers\Admin\MenuNavigationController;
use App\Http\Controllers\Admin\PosTransactionController;
use App\Http\Controllers\Admin\StudentBarcodeController;
use App\Http\Controllers\Admin\ApplicationMenuController;
use App\Http\Controllers\Admin\CashFlowCategoryController;
use App\Http\Controllers\Admin\OrderItemHistoryController;
use App\Http\Controllers\Admin\PpdbRegistrationController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\ReportStudyGradeController;
use App\Http\Controllers\Admin\ReportBillStudentController;
use App\Http\Controllers\Admin\ReportTransactionController;
use App\Http\Controllers\Admin\StudentGraduationController;
use App\Http\Controllers\Admin\SubMenuNavigationController;
use App\Http\Controllers\Admin\ApplicationSettingController;
use App\Http\Controllers\Admin\StudentAchievementController;
use App\Http\Controllers\Admin\InformationCategoryController;
use App\Http\Controllers\User\WaliSettingLimitSaldoController;
use App\Http\Controllers\Admin\StudentCounselingScoreController;
use App\Http\Controllers\Admin\ReportStudentCounselingScoreController;

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
// Admin audit route (legacy UI)
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('audit', [App\Http\Controllers\Admin\AuditController::class, 'index'])->name('admin.audit');
});
// start wali santri & asatidz (CT-Mobile)
// add route group prefix and middleware

Route::any('wali/{any?}', function ($any = null) {
    return redirect('/ct-mobile/' . ($any ? $any : ''), 301);
})->where('any', '.*');

Route::prefix('ct-mobile')->group(function () {
    Route::get('/', [WaliDashboardController::class, 'app'])->name('wali.index');
    Route::get('login', [WaliDashboardController::class, 'app'])->name('wali.login');
    Route::post('login', [CtMobileAuthController::class, 'authenticate'])->name('wali.authenticate');
    Route::post('logout', [CtMobileAuthController::class, 'logout'])->name('wali.logout');
    Route::get('logout', [CtMobileAuthController::class, 'logout']);
    Route::get('register', [CtMobileAuthController::class, 'register'])->name('wali.register');
    Route::post('register', [CtMobileAuthController::class, 'store'])->name('wali.register.store');

    Route::middleware('wali')->group(function () {
        Route::get('app', [WaliDashboardController::class, 'app'])->name('wali.app');
        Route::post('/switch-student/{id}', [WaliDashboardController::class, 'switchStudent'])->name('switch-student');

    // SPA Fallback
    Route::get('/app/{any?}', [WaliDashboardController::class, 'app'])->where('any', '.*')->name('app');
        Route::get('history', [WaliDashboardController::class, 'app'])->name('wali.history');
        Route::get('bills', [WaliDashboardController::class, 'app'])->name('wali.bills');
        Route::get('bills/{id}', [WaliDashboardController::class, 'app'])->name('wali.bill-detail');
        Route::post('checkout', [WaliDashboardController::class, 'checkout'])->name('wali.checkout');
        Route::get('payment/{id}', [WaliDashboardController::class, 'app'])->name('wali.payment');
        Route::post('payment/{id}/upload-proof', [WaliDashboardController::class, 'uploadProof'])->name('wali.upload-proof');
        Route::get('limit', [WaliDashboardController::class, 'app'])->name('wali.limit');
        Route::post('limit', [WaliDashboardController::class, 'updateLimit'])->name('wali.update-limit');
        Route::get('topup', [WaliDashboardController::class, 'app'])->name('wali.topup');
        Route::post('topup', [WaliDashboardController::class, 'storeTopup'])->name('wali.store-topup');
        Route::get('profile', [WaliDashboardController::class, 'app'])->name('wali.profile');
        Route::post('profile', [WaliDashboardController::class, 'updateProfile'])->name('wali.update-profile');
        Route::post('password', [WaliDashboardController::class, 'updatePassword'])->name('wali.update-password');
        Route::get('dashboard', [WaliDashboardController::class, 'app'])->name('wali.dashboard');
        Route::get('news/{id}', [WaliDashboardController::class, 'newsDetail'])->name('wali.news-detail');
        Route::get('tahfidz', [WaliDashboardController::class, 'tahfidz'])->name('wali.tahfidz');
        Route::get('grades', [WaliDashboardController::class, 'grades'])->name('wali.grades');
        Route::get('schedule', [WaliDashboardController::class, 'schedule'])->name('wali.schedule');
        Route::resource('ppdb', WaliPpdbController::class)->names('wali.ppdb');
        Route::get('ppdb-history/pay/{id}', [PpdbHistoryController::class, 'pay'])->name('wali.ppdb-history.pay');
        Route::resource('ppdb-history', PpdbHistoryController::class)->names('wali.ppdb-history');
        Route::resource('setting-limit-saldo', WaliSettingLimitSaldoController::class)
            ->names('wali.setting-limit-saldo');
    });
});




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
    Route::get('payment-rate/get-student', [PaymentRateController::class, 'getStudent'])
        ->name('payment-rate.get-student');
    Route::get('select2', [Select2Controller::class, 'index'])->name('select2');
    Route::resource('permission', PermissionController::class, ['except' => ['show']]);
    Route::resource('role', RoleController::class);
    Route::resource('admin', AdminController::class);
    Route::post('user/import', [UserController::class, 'import'])->name('user.import');
    Route::resource('user', UserController::class);
    Route::resource('school', SchoolController::class);
    Route::resource('classroom', ClassroomController::class, ['except' => ['index', 'show']]);
    Route::resource('academic-year', AcademicYearController::class, ['except' => ['show']]);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('student/school/{id}', [StudentController::class, 'getClassrooms'])->name('student.get-classroom');
    Route::post('student/import', [StudentController::class, 'import'])->name('student.import');
    Route::resource('student', StudentController::class);
    Route::resource('tahfidz', TahfidzController::class);
    Route::resource('category-item', CategoryItemController::class);
    Route::post('item/import', [ItemController::class, 'import'])->name('item.import');
    Route::resource('item', ItemController::class);
    Route::resource('stock-history', StockHistoryController::class);
    Route::resource('contact', ContactController::class);

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
    Route::get('order-item/get-daily-transaction', [OrderItemController::class, 'getDailyTransaction'])
        ->name('order-item.get-daily-transaction');
    Route::resource('order-item', OrderItemController::class);
    Route::get('order-item-history/print/{id}', [OrderItemHistoryController::class, 'print'])->name('order-item-history.print');
    Route::resource('order-item-history', OrderItemHistoryController::class);
    Route::get('report-pos', [ReportPosController::class, 'index'])->name('report-pos.index');
    Route::get('pos-transaction', [PosTransactionController::class, 'index'])->name('pos-transaction.index');

    Route::resource('bill-item', BillItemController::class);
    Route::resource('bill-type', BillTypeController::class);
    Route::delete('delete-student-bill', [BillController::class, 'deleteStudentBill'])->name('delete-student-bill');
    Route::get('payment-rate/get-bill-details', [PaymentRateController::class, 'getBillDetails'])
        ->name('payment-rate.get-bill-details');
    Route::post('payment-rate/delete-bill', [PaymentRateController::class, 'deleteBill'])
        ->name('payment-rate.delete-bill');
    Route::post('payment-rate/delete-bills-mass', [PaymentRateController::class, 'deleteBillsMass'])
        ->name('payment-rate.delete-bills-mass');
    Route::post('payment-rate/update-bill', [PaymentRateController::class, 'updateBill'])
        ->name('payment-rate.update-bill');
    Route::resource('payment-rate', PaymentRateController::class);
    Route::post('bill.change-status', [BillController::class, 'changeStatus'])->name('bill.change-status');
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
    
    // student perizinan backoffice
    Route::resource('student-permit', \App\Http\Controllers\Admin\StudentPermitController::class);

    // asrama backoffice
    Route::resource('asrama', \App\Http\Controllers\Admin\AsramaController::class);
    // home
    Route::resource('contact', ContactController::class, ['only' => ['index', 'store']])->names('contact');
    Route::resource('application-setting', ApplicationSettingController::class, ['only' => ['index', 'store']])
        ->names('application-setting');

    // start saldo history
    Route::resource('saldo-bank', SaldoBankController::class, ['only' => ['index', 'edit', 'update']])->names('saldo-bank');
    Route::resource('saving-bank', SavingBankController::class, ['only' => ['index', 'edit', 'update']])->names('saving-bank');
    Route::post('saldo-history/status-payment/{id}', [SaldoHistoryController::class, 'updateStatusPayment'])->name('saldo-history.status-payment');
    Route::post('saldo-history/import', [SaldoHistoryController::class, 'import'])->name('saldo-history.import');
    Route::resource('saldo-history', SaldoHistoryController::class);
    Route::post('saving-history/status-payment/{id}', [SavingHistoryController::class, 'updateStatusPayment'])->name('saving-history.status-payment');
    Route::resource('saving-history', SavingHistoryController::class);

    // start schedule
    Route::resource('schedule', ScheduleController::class);

    Route::resource('help', HelpController::class);
    Route::post('officer/status/{id}', [OfficerController::class, 'status'])->name('officer.status');
    Route::resource('officer', OfficerController::class);
    Route::resource('app-information', AppInformationController::class, ['only' => ['index', 'store']])->names('app-information');

    // start study
    Route::resource('study', StudyController::class);
    Route::resource('semester', SemesterController::class);
    Route::resource('study-grade', StudyGradeController::class);
    Route::post('application-menu/status/{id}', [ApplicationMenuController::class, 'status'])->name('application-menu.status');
    Route::get('application-menu/get-class-levels', [ApplicationMenuController::class, 'getClassLevels'])->name('application-menu.get-class-levels');
    Route::resource('application-menu', ApplicationMenuController::class);

    // start report bill
    Route::post('report-bill/send-bill-notification', [ReportBillController::class, 'sendBillNotification'])
        ->name('report-bill.send-bill-notification');
    Route::get('report-bill/get-classroom', [ReportBillController::class, 'getClassroom'])
        ->name('report-bill.get-classroom');
    Route::get('report-bill/get-data', [ReportBillController::class, 'getData'])->name('report-bill.get-data');
    Route::get('report-bill/send-wa/{id}', [ReportBillController::class, 'sendWa'])->name('report-bill.send-wa');
    Route::get('report-bill/{id}/export', [ReportBillController::class, 'export'])->name('report-bill.export');
        
    // Mutasi Pindah Unit
    Route::resource('unit-transfer-config', App\Http\Controllers\Admin\UnitTransferConfigController::class);

    // API untuk mengambil kelas berdasarkan sekolah
    Route::get('/api/classrooms/{school_id}', function ($school_id) {
        $classrooms = \App\Models\Classroom::where('school_id', $school_id)->orderBy('name', 'asc')->get();
        return response()->json($classrooms);
    })->name('api.classrooms');
    Route::resource('report-bill', ReportBillController::class, ['only' => ['index', 'show']])->names('report-bill');
    Route::post('send-bill-whatsapp-notification', [ReportBillStudentController::class, 'sendBillWhatsappNotification'])
        ->name('send-bill-whatsapp-notification');
    Route::get('report-bill-student/export', [ReportBillStudentController::class, 'exportXlsx'])->name('report-bill-student.export');
    Route::get('report-bill-student/share', [ReportBillStudentController::class, 'share'])->name('report-bill-student.share');
    Route::resource('report-bill-student', ReportBillStudentController::class, ['only' => ['index']])->names('report-bill-student');
    // end report bill

    // start report student
    Route::get('report-student', [ReportStudentController::class, 'index'])->name('report-student.index');
    Route::get('report-student/export', [ReportStudentController::class, 'export'])->name('report-student.export');
    Route::get('report-tahfidz', [ReportTahfidzController::class, 'index'])->name('report-tahfidz.index');
    Route::get('report-tahfidz/export', [ReportTahfidzController::class, 'export'])->name('report-tahfidz.export');
    Route::get('report-student-counseling-score', [ReportStudentCounselingScoreController::class, 'index'])
        ->name('report-student-counseling-score.index');
    Route::get('report-student-counseling-score/export', [ReportStudentCounselingScoreController::class, 'export'])
        ->name('report-student-counseling-score.export');
    Route::get('report-app-fee', [ReportAppFeeController::class, 'index'])->name('report-app-fee.index');
    Route::delete('report-saldo/delete/{id}', [ReportSaldoController::class, 'destroy'])->name('report-saldo.destroy');
    Route::get('report-saldo', [ReportSaldoController::class, 'index'])->name('report-saldo.index');
    Route::get('report-saldo/export', [ReportSaldoController::class, 'export'])->name('report-saldo.export');
    Route::get('report-study-grade', [ReportStudyGradeController::class, 'index'])->name('report-study-grade.index');
    Route::get('report-study-grade/export', [ReportStudyGradeController::class, 'export'])->name('report-study-grade.export');
    Route::get('report-user', [ReportUserController::class, 'index'])->name('report-user.index');
    Route::get('report-user/export', [ReportUserController::class, 'export'])->name('report-user.export');
    // Route::get('report-student/search-student', [ReportStudentController::class, 'searchStudent'])
    //     ->name('report-student.search-student');

    Route::resource('grade-promotion', GradePromotionController::class, ['only' => ['index', 'store']]);
    Route::resource('student-graduation', StudentGraduationController::class, ['only' => ['index', 'store']]);
    Route::post('bank/status/{id}', [BankController::class, 'status'])->name('bank.status');
    Route::resource('bank', BankController::class, ['except' => ['show']]);
    Route::get('student-barcode/change-barcode/{id}', [StudentBarcodeController::class, 'changeBarcode'])
        ->name('student-barcode.change-barcode');
    Route::resource('student-barcode', StudentBarcodeController::class, ['only' => ['index', 'create', 'store']]);
    Route::get('report-transaction/export', [ReportTransactionController::class, 'export'])->name('report-transaction.export');
    Route::resource('report-transaction', ReportTransactionController::class, ['only' => ['index', 'destroy']]);


    // start ppdb
    Route::resource('ppdb-type', PpdbTypeController::class);
    Route::resource('ppdb', PpdbController::class);
    Route::resource('ppdb-registration', PpdbRegistrationController::class);
    Route::resource('registrations', RegistrationController::class)->only(['index', 'show']);
    Route::resource('menu-navigation', MenuNavigationController::class);
    Route::resource('submenu-navigation', SubMenuNavigationController::class);

    // ppdb new
    Route::resource('ppdb-waves', PpdbWavesController::class);
    Route::resource('ppdb-track', PpdbTrackController::class);

    // PSB Management (Penerimaan Santri Baru)
    Route::prefix('psb')->name('psb.')->group(function () {
        // Registration Management
        Route::get('/', [\App\Http\Controllers\Admin\AdminPsbController::class, 'index'])->name('index');
        Route::get('/registrations/{id}', [\App\Http\Controllers\Admin\AdminPsbController::class, 'show'])->name('show');
        Route::put('/registrations/{id}/status', [\App\Http\Controllers\Admin\AdminPsbController::class, 'updateStatus'])->name('update-status');
        Route::post('/verify-kta/{userId}', [\App\Http\Controllers\Admin\AdminPsbController::class, 'verifyKta'])->name('verify-kta');
        Route::get('/classrooms/{schoolId}', [\App\Http\Controllers\Admin\AdminPsbController::class, 'getClassrooms'])->name('classrooms');

        // Wave Management
        Route::get('/waves/{id}', [\App\Http\Controllers\Admin\AdminWaveController::class, 'show'])->name('waves.show');

        // Track Management (nested under Wave)
        Route::post('/waves/{waveId}/tracks', [\App\Http\Controllers\Admin\AdminWaveController::class, 'storeTrack'])->name('waves.tracks.store');
        Route::get('/waves/{waveId}/tracks/{trackId}', [\App\Http\Controllers\Admin\AdminWaveController::class, 'getTrack'])->name('waves.tracks.get');
        Route::put('/waves/{waveId}/tracks/{trackId}', [\App\Http\Controllers\Admin\AdminWaveController::class, 'updateTrack'])->name('waves.tracks.update');
        Route::delete('/waves/{waveId}/tracks/{trackId}', [\App\Http\Controllers\Admin\AdminWaveController::class, 'destroyTrack'])->name('waves.tracks.destroy');
    });

    // start cashflow
    Route::resource('cashflow-category', CashFlowCategoryController::class);
    // Define the routes
    Route::post('cashflow/approve/{id}', [CashFlowController::class, 'approve'])->name('cashflow.approve');
    Route::post('cashflow/reject/{id}', [CashFlowController::class, 'reject'])->name('cashflow.reject');
    Route::resource('cashflow', CashFlowController::class);
});


Route::get('privacy-policy', [PrivacyPolicyController::class, 'index'])->name('privacy-policy');
Route::get('term-condition', [PrivacyPolicyController::class, 'termCondition'])->name('term-condition');
Route::get('about-us', [PrivacyPolicyController::class, 'aboutUs'])->name('about-us');
Route::get('transaction/invoice/{id}', [TransactionController::class, 'invoice'])->name('transaction.invoice');

Route::controller(App\Http\Controllers\User\PaymentCheckController::class)->group(function () {
    Route::get('/status-pembayaran', 'index')->name('public.spp.index');
    Route::get('/status-pembayaran/get-classes', 'getClasses')->name('public.spp.get-classes');
});
 
// SPA Fallback for static PWA assets (bypasses missing public/ symlinks)
Route::get('portalwalisantri/{any}', function ($any) {
    // Only process typical static assets
    if (!preg_match('/\.(css|js|json|png|ico|svg|woff2?)$/i', $any)) {
        abort(404);
    }
    
    // Extract just the filename to look for it inside the actual build folders
    $filename = basename($any);
    
    // Try multiple possible locations
    $paths = [
        base_path("portalwalisantri/dist/client/assets/{$filename}"),
        base_path("portalwalisantri/dist/assets/{$filename}"),
        base_path("portalwalisantri/dist/client/{$filename}"),
        base_path("portalwalisantri/dist/{$filename}"),
        base_path("portalwalisantri/{$any}") // direct path fallback
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            $mime = 'text/plain';
            if (str_ends_with($path, '.css')) $mime = 'text/css';
            elseif (str_ends_with($path, '.js')) $mime = 'application/javascript';
            elseif (str_ends_with($path, '.json')) $mime = 'application/json';
            elseif (str_ends_with($path, '.png')) $mime = 'image/png';
            elseif (str_ends_with($path, '.svg')) $mime = 'image/svg+xml';
            elseif (str_ends_with($path, '.ico')) $mime = 'image/x-icon';
            else {
                $info = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($info, $path);
                finfo_close($info);
            }
            
            return response()->file($path, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=31536000'
            ]);
        }
    }
    abort(404);
})->where('any', '.*');

// Ultimate bypass for Nginx static file rules (Nginx won't block this because it doesn't end in .js or .css in the path)
Route::get('pwa-asset', function (\Illuminate\Http\Request $request) {
    $file = $request->query('f');
    if (!$file) abort(404);
    
    $filename = basename($file);
    
    $paths = [
        base_path("portalwalisantri/dist/client/{$file}"),
        base_path("portalwalisantri/dist/{$file}"),
        base_path("portalwalisantri/dist/client/assets/{$filename}"),
        base_path("portalwalisantri/dist/assets/{$filename}"),
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            $mime = 'text/plain';
            if (str_ends_with($path, '.css')) $mime = 'text/css';
            elseif (str_ends_with($path, '.js')) $mime = 'application/javascript';
            elseif (str_ends_with($path, '.json')) $mime = 'application/json';
            elseif (str_ends_with($path, '.png')) $mime = 'image/png';
            elseif (str_ends_with($path, '.svg')) $mime = 'image/svg+xml';
            elseif (str_ends_with($path, '.ico')) $mime = 'image/x-icon';
            
            return response()->file($path, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=31536000'
            ]);
        }
    }
    abort(404);
})->name('pwa-asset');

Route::get('file-asset', function (\Illuminate\Http\Request $request) {
    $p = $request->query('p');
    if (!$p) abort(404);
    
    // remove leading storage/ if present
    $p = preg_replace('/^storage\//', '', $p);
    
    $path = storage_path('app/public/' . $p);
    if (!file_exists($path)) {
        abort(404);
    }
    
    $mime = 'application/octet-stream';
    $pLower = strtolower($p);
    if (str_ends_with($pLower, '.jpg') || str_ends_with($pLower, '.jpeg')) $mime = 'image/jpeg';
    elseif (str_ends_with($pLower, '.png')) $mime = 'image/png';
    elseif (str_ends_with($pLower, '.svg')) $mime = 'image/svg+xml';
    elseif (str_ends_with($pLower, '.webp')) $mime = 'image/webp';
    elseif (str_ends_with($pLower, '.gif')) $mime = 'image/gif';
    else {
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($info, $path);
        finfo_close($info);
    }
    
    return response()->file($path, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000'
    ]);
})->name('file-asset');

Route::get('public/report-bill-student/{token}', [App\Http\Controllers\Public\PublicReportBillStudentController::class, 'index'])->name('public.report-bill-student.index');
