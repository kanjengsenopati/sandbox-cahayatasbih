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
use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\Admin\KaryawanController;
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
use App\Http\Controllers\Admin\OutletHandoverController;
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
// Admin audit route
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('audit/sync', [App\Http\Controllers\Admin\AuditController::class, 'syncIndex'])->name('admin.audit.sync');
    Route::get('audit/diagnostics', [App\Http\Controllers\Admin\AuditController::class, 'diagnosticsIndex'])->name('admin.audit.diagnostics');
    Route::get('audit/diagnostics/ai-insight', [App\Http\Controllers\Admin\AuditController::class, 'ajaxAiInsight'])->name('admin.audit.diagnostics.ai-insight');
    Route::get('audit/duplicate-students', [App\Http\Controllers\Admin\AuditController::class, 'duplicatesIndex'])->name('admin.audit.duplicates');

    // Redirect old route for compatibility
    Route::get('audit', function() {
        return redirect()->route('admin.audit.sync');
    })->name('admin.audit');

    Route::post('sync-master', [App\Http\Controllers\Admin\AuditController::class, 'syncMaster'])->name('admin.sync-master');
    Route::post('audit/sync-selected-students', [App\Http\Controllers\Admin\AuditController::class, 'syncSelectedStudents'])->name('admin.audit.sync-selected-students');
    Route::post('audit/merge-students', [App\Http\Controllers\Admin\AuditController::class, 'mergeStudents'])->name('admin.merge-students');
});
// start wali santri & penanggung jawab (CT-Mobile)
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




Route::get('/laporpak/{any?}', [WaliDashboardController::class, 'app'])->where('any', '.*')->name('laporpak');
Route::get('/admin/laporpak/{any?}', [WaliDashboardController::class, 'app'])->where('any', '.*')->name('admin.laporpak');

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
    Route::get('academic', [App\Http\Controllers\Admin\AcademicController::class, 'index'])->name('academic.index');
    Route::get('school', function (\Illuminate\Http\Request $request) {
        if ($request->ajax()) {
            return app()->call([\App\Http\Controllers\Admin\SchoolController::class, 'index']);
        }
        return redirect()->route('academic.index', ['tab' => 'school']);
    })->name('school.index');
    Route::get('academic-year', function (\Illuminate\Http\Request $request) {
        if ($request->ajax()) {
            return app()->call([\App\Http\Controllers\Admin\AcademicYearController::class, 'index']);
        }
        return redirect()->route('academic.index', ['tab' => 'academic-year']);
    })->name('academic-year.index');
    Route::get('semester', function (\Illuminate\Http\Request $request) {
        if ($request->ajax()) {
            return app()->call([\App\Http\Controllers\Admin\SemesterController::class, 'index']);
        }
        return redirect()->route('academic.index', ['tab' => 'semester']);
    })->name('semester.index');
    Route::get('study', function (\Illuminate\Http\Request $request) {
        if ($request->ajax()) {
            return app()->call([\App\Http\Controllers\Admin\StudyController::class, 'index']);
        }
        return redirect()->route('academic.index', ['tab' => 'study']);
    })->name('study.index');
    Route::get('grade-promotion', function (\Illuminate\Http\Request $request) {
        if ($request->ajax()) {
            return app()->call([\App\Http\Controllers\Admin\GradePromotionController::class, 'index']);
        }
        return redirect()->route('academic.index', ['tab' => 'grade-promotion']);
    })->name('grade-promotion.index');
    Route::get('student-graduation', function (\Illuminate\Http\Request $request) {
        if ($request->ajax()) {
            return app()->call([\App\Http\Controllers\Admin\StudentGraduationController::class, 'index']);
        }
        return redirect()->route('academic.index', ['tab' => 'student-graduation']);
    })->name('student-graduation.index');

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
    Route::post('role/{role}/assign', [RoleController::class, 'assignUsers'])->name('role.assign');
    Route::resource('role', RoleController::class);
    Route::post('admin/{admin}/impersonate', [AdminController::class, 'impersonate'])->name('admin.impersonate');
    Route::post('admin/stop-impersonating', [AdminController::class, 'stopImpersonating'])->name('admin.stop-impersonating');
    Route::get('admin/scope-akses', [AdminController::class, 'scopeAkses'])->name('admin.scope-akses');
    Route::post('admin/scope-akses/{scope}/assign', [AdminController::class, 'assignScopeUsers'])->name('admin.scope-akses.assign');
    Route::resource('admin', AdminController::class);
    Route::post('user/bulk-update-status', [UserController::class, 'bulkUpdateStatus'])->name('user.bulk-update-status');
    Route::post('user/check-duplicate', [UserController::class, 'checkDuplicate'])->name('user.check-duplicate');
    Route::post('user/{user}/verify', [UserController::class, 'verify'])->name('user.verify');
    Route::post('user/bulk-delete', [UserController::class, 'bulkDestroy'])->name('user.bulk-delete');
    Route::resource('user', UserController::class);
    Route::post('school/{school}/assign', [SchoolController::class, 'assignUsers'])->name('school.assign');
    Route::resource('school', SchoolController::class);
    Route::post('outlet/{outlet}/assign', [OutletController::class, 'assignUsers'])->name('outlet.assign');
    Route::resource('classroom', ClassroomController::class, ['except' => ['index', 'show']]);
    Route::resource('academic-year', AcademicYearController::class, ['except' => ['show']]);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('student/school/{id}', [StudentController::class, 'getClassrooms'])->name('student.get-classroom');
    Route::post('student/import-preview', [StudentController::class, 'importPreview'])->name('student.import-preview');
    Route::post('student/import-confirm', [StudentController::class, 'importConfirm'])->name('student.import-confirm');
    Route::post('student/bulk-delete', [StudentController::class, 'bulkDestroy'])->name('student.bulk-delete');
    Route::resource('student', StudentController::class);
    Route::resource('tahfidz', TahfidzController::class);
    Route::resource('category-item', CategoryItemController::class);
    Route::post('item/import', [ItemController::class, 'import'])->name('item.import');
    Route::resource('item', ItemController::class);
    Route::resource('outlet', OutletController::class);
    Route::resource('karyawan', KaryawanController::class);
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
    Route::redirect('report-pos', 'pos-transaction')->name('report-pos.index');
    Route::get('pos-transaction', [PosTransactionController::class, 'index'])->name('pos-transaction.index');
    Route::delete('pos-transaction/{pos_transaction}', [PosTransactionController::class, 'destroy'])->name('pos-transaction.destroy');
    Route::get('outlet-handover/pending-amount/{outlet_id}', [OutletHandoverController::class, 'getPendingAmount'])->name('outlet-handover.pending-amount');
    Route::resource('outlet-handover', OutletHandoverController::class);

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
    Route::post('payment-rate/{id}/generate', [PaymentRateController::class, 'generate'])
        ->name('payment-rate.generate');
    Route::resource('payment-rate', PaymentRateController::class);
    Route::get('bill/download-template', [BillController::class, 'downloadTemplate'])->name('bill.download-template');
    Route::post('bill/preview-import', [BillController::class, 'previewImport'])->name('bill.preview-import');
    Route::post('bill/confirm-import', [BillController::class, 'confirmImport'])->name('bill.confirm-import');
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

    // payroll backoffice
    Route::get('payroll', [\App\Http\Controllers\Admin\PayrollController::class, 'index'])->name('payroll.index');
    Route::post('payroll/process', [\App\Http\Controllers\Admin\PayrollController::class, 'process'])->name('payroll.process');
    Route::get('payroll-settings', [\App\Http\Controllers\Admin\PayrollController::class, 'settings'])->name('payroll.settings');
    Route::post('payroll-settings/save', [\App\Http\Controllers\Admin\PayrollController::class, 'saveSetting'])->name('payroll.settings.save');
    Route::get('payroll/{id}', [\App\Http\Controllers\Admin\PayrollController::class, 'show'])->name('payroll.show');
    Route::post('payroll/{id}/approve', [\App\Http\Controllers\Admin\PayrollController::class, 'approve'])->name('payroll.approve');
    Route::post('payroll/{id}/pay', [\App\Http\Controllers\Admin\PayrollController::class, 'pay'])->name('payroll.pay');

    // working shift backoffice
    Route::post('working-shift/{id}/status', [\App\Http\Controllers\Admin\WorkingShiftController::class, 'status'])->name('working-shift.status');
    Route::resource('working-shift', \App\Http\Controllers\Admin\WorkingShiftController::class);

    // biometric device backoffice
    Route::post('biometric-device/{id}/status', [\App\Http\Controllers\Admin\BiometricDeviceController::class, 'status'])->name('biometric-device.status');
    Route::resource('biometric-device', \App\Http\Controllers\Admin\BiometricDeviceController::class);

    // biometric mapping backoffice
    Route::get('biometric-mapping/kiosk', [\App\Http\Controllers\Admin\BiometricMappingController::class, 'kiosk'])->name('biometric-mapping.kiosk');
    Route::get('biometric-mapping/descriptors', [\App\Http\Controllers\Admin\BiometricMappingController::class, 'descriptors'])->name('biometric-mapping.descriptors');
    Route::post('biometric-mapping/scan', [\App\Http\Controllers\Admin\BiometricMappingController::class, 'scan'])->name('biometric-mapping.scan');
    Route::get('biometric-mapping/search-users', [\App\Http\Controllers\Admin\BiometricMappingController::class, 'searchUsers'])->name('biometric-mapping.search-users');
    Route::post('biometric-mapping/manual-capture', [\App\Http\Controllers\Admin\BiometricMappingController::class, 'manualCapture'])->name('biometric-mapping.manual-capture');
    Route::resource('biometric-mapping', \App\Http\Controllers\Admin\BiometricMappingController::class)->only(['index', 'store', 'destroy']);

    // asrama backoffice
    Route::resource('asrama', \App\Http\Controllers\Admin\AsramaController::class);
    // home
    Route::resource('contact', ContactController::class, ['only' => ['index', 'store']])->names('contact');
    Route::resource('application-setting', ApplicationSettingController::class, ['only' => ['index', 'store']])
        ->names('application-setting');

    // Desain & Cetak Kartu (Templated)
    Route::get('student-card-setting', [\App\Http\Controllers\Admin\StudentCardSettingController::class, 'index'])->name('student-card-setting.index');
    Route::post('student-card-setting/templates', [\App\Http\Controllers\Admin\StudentCardSettingController::class, 'storeTemplate'])->name('student-card-setting.store-template');
    Route::put('student-card-setting/templates/{id}', [\App\Http\Controllers\Admin\StudentCardSettingController::class, 'updateTemplate'])->name('student-card-setting.update-template');
    Route::delete('student-card-setting/templates/{id}', [\App\Http\Controllers\Admin\StudentCardSettingController::class, 'destroyTemplate'])->name('student-card-setting.destroy-template');
    Route::post('student-card-setting/templates/{id}/toggle-active', [\App\Http\Controllers\Admin\StudentCardSettingController::class, 'toggleActive'])->name('student-card-setting.toggle-active');
    Route::get('student-card-setting/templates/{id}/design', [\App\Http\Controllers\Admin\StudentCardSettingController::class, 'design'])->name('student-card-setting.design');
    Route::post('student-card-setting/templates/{id}/design', [\App\Http\Controllers\Admin\StudentCardSettingController::class, 'storeDesign'])->name('student-card-setting.store-design');
    Route::post('student-card-setting/print', [\App\Http\Controllers\Admin\StudentCardSettingController::class, 'print'])->name('student-card-setting.print');
    Route::get('student-card-setting/get-students', [\App\Http\Controllers\Admin\StudentCardSettingController::class, 'getStudents'])->name('student-card-setting.get-students');

    // start saldo history
    Route::resource('saldo-bank', SaldoBankController::class, ['only' => ['index', 'edit', 'update']])->names('saldo-bank');
    Route::resource('saving-bank', SavingBankController::class, ['only' => ['index', 'edit', 'update']])->names('saving-bank');
    Route::post('saldo-history/status-payment/{id}', [SaldoHistoryController::class, 'updateStatusPayment'])->name('saldo-history.status-payment');
    Route::post('saldo-history/import', [SaldoHistoryController::class, 'import'])->name('saldo-history.import');
    Route::delete('saldo-history/record/{id}', [SaldoHistoryController::class, 'deleteHistory'])->name('saldo-history.delete-history');
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
    Route::get('report-bill-student/get-bill-types', [ReportBillStudentController::class, 'getBillTypes'])->name('report-bill-student.get-bill-types');
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

    // start report audit
    Route::get('report-audit', [App\Http\Controllers\Admin\ReportAuditController::class, 'index'])->name('report-audit.index');
    Route::get('report-audit/export', [App\Http\Controllers\Admin\ReportAuditController::class, 'export'])->name('report-audit.export');

    // start report attendance
    Route::get('report-attendance', [\App\Http\Controllers\Admin\ReportAttendanceController::class, 'index'])->name('report-attendance.index');
    Route::post('report-attendance/{id}/approve', [\App\Http\Controllers\Admin\ReportAttendanceController::class, 'approve'])->name('report-attendance.approve');
    Route::post('report-attendance/{id}/reject', [\App\Http\Controllers\Admin\ReportAttendanceController::class, 'reject'])->name('report-attendance.reject');
    // Route::get('report-student/search-student', [ReportStudentController::class, 'searchStudent'])
    //     ->name('report-student.search-student');

    Route::resource('grade-promotion', GradePromotionController::class, ['only' => ['index', 'store']]);
    Route::get('student-graduation/get-classroom', [StudentGraduationController::class, 'getClassroom'])
        ->name('student-graduation.get-classroom');
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
    Route::get('report-profit-loss', [App\Http\Controllers\Admin\ProfitLossReportController::class, 'index'])->name('report-profit-loss.index');
    Route::post('report-profit-loss/store-expense', [App\Http\Controllers\Admin\ProfitLossReportController::class, 'storeExpense'])->name('report-profit-loss.store-expense');
    Route::delete('report-profit-loss/delete-expense/{id}', [App\Http\Controllers\Admin\ProfitLossReportController::class, 'destroyExpense'])->name('report-profit-loss.destroy-expense');
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
    
    // Prioritaskan public_path (lokasi build baru) sebelum base_path (lokasi build lama)
    $paths = [
        public_path("portalwalisantri/dist/{$file}"),
        public_path("portalwalisantri/dist/assets/{$filename}"),
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
            elseif (str_ends_with($path, '.html')) $mime = 'text/html';
            elseif (str_ends_with($path, '.webmanifest')) $mime = 'application/manifest+json';
            
            $headers = [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=31536000'
            ];
            
            // Service Worker harus tidak di-cache agresif agar update bisa terdeteksi
            if (str_ends_with($path, 'sw.js')) {
                $headers['Service-Worker-Allowed'] = '/';
                $headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
            }
            
            return response()->file($path, $headers);
        }
    }
    abort(404);
})->name('pwa-asset');

// Route dependency Workbox yang diimpor oleh Service Worker sw.js
Route::get('workbox-{hash}.js', function ($hash) {
    $path = base_path("portalwalisantri/dist/client/workbox-{$hash}.js");
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=31536000',
            'Service-Worker-Allowed' => '/'
        ]);
    }
    abort(404);
})->where('hash', '[a-zA-Z0-9]+');

// Route untuk manifest.webmanifest yang diminta relatif terhadap Service Worker scope
Route::get('manifest.webmanifest', function () {
    $path = base_path("portalwalisantri/dist/client/manifest.webmanifest");
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=31536000'
        ]);
    }
    abort(404);
});

// Route untuk sw.js agar dilayani di level root domain dengan static filesystem bypass
Route::get('sw.js', function () {
    $paths = [
        public_path("portalwalisantri/dist/sw.js"),
        base_path("portalwalisantri/dist/client/sw.js"),
        base_path("portalwalisantri/dist/sw.js"),
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            return response()->file($path, [
                'Content-Type' => 'application/javascript',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Service-Worker-Allowed' => '/'
            ]);
        }
    }
    abort(404);
});

// Route untuk firebase-messaging-sw.js agar dilayani di level root domain dan terintegrasi dengan ENV config
Route::get('firebase-messaging-sw.js', function () {
    $paths = [
        base_path("portalwalisantri/dist/client/firebase-messaging-sw.js"),
        base_path("portalwalisantri/dist/firebase-messaging-sw.js"),
        public_path("portalwalisantri/dist/firebase-messaging-sw.js"),
        base_path("portalwalisantri/public/firebase-messaging-sw.js"),
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            $content = file_get_contents($path);
            
            // Suntikkan konfigurasi Firebase dari Laravel config secara dinamis
            $config = config('services.firebase');
            $configJs = json_encode([
                'apiKey' => $config['api_key'],
                'authDomain' => $config['auth_domain'],
                'projectId' => $config['project_id'],
                'storageBucket' => $config['storage_bucket'],
                'messagingSenderId' => $config['messaging_sender_id'],
                'appId' => $config['app_id'],
            ], JSON_PRETTY_PRINT);
            
            // Gantikan const firebaseConfig = { ... }
            $content = preg_replace(
                '/const firebaseConfig = \{.*?\};/s',
                "const firebaseConfig = {$configJs};",
                $content
            );
            
            return response($content, 200, [
                'Content-Type' => 'application/javascript',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Service-Worker-Allowed' => '/'
            ]);
        }
    }
    abort(404);
});

Route::get('file-asset', function (\Illuminate\Http\Request $request) {
    $p = $request->query('p');
    if (!$p) abort(404);
    
    // remove leading storage/ if present
    $p = preg_replace('/^storage\//', '', $p);
    
    $path = storage_path('app/public/' . $p);
    if (!file_exists($path)) {
        $masterUrl = config('app.master_url');
        if ($masterUrl) {
            return redirect()->away(rtrim($masterUrl, '/') . '/file-asset?p=' . urlencode($p));
        }
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
Route::get('s/{code}', [App\Http\Controllers\Public\ShortUrlController::class, 'show'])->name('public.short-url.show');
