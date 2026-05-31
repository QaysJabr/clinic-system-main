<?php

use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\AppointmentCalendarController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentSlotController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ClinicalDashboardController;
use App\Http\Controllers\ClinicSettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorEarningController;
use App\Http\Controllers\DoctorReportController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\ExcelExportController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpensePaymentController;
use App\Http\Controllers\ExportDownloadController;
use App\Http\Controllers\InternalChatController;
use App\Http\Controllers\Inventory\InventoryDashboardController;
use App\Http\Controllers\Inventory\InventoryItemController;
use App\Http\Controllers\Inventory\InventoryProcedureTemplateController;
use App\Http\Controllers\Inventory\InventoryPurchaseController;
use App\Http\Controllers\Inventory\InventoryReportController;
use App\Http\Controllers\Inventory\InventoryStockMovementController;
use App\Http\Controllers\Inventory\InventorySupplierController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\LocaleApplyController;
use App\Http\Controllers\OgImageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientAccountController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientEmrController;
use App\Http\Controllers\PatientLookupController;
use App\Http\Controllers\PatientPortalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayrollRunController;
use App\Http\Controllers\Platform\PlatformClinicController;
use App\Http\Controllers\Platform\PlatformExportController;
use App\Http\Controllers\Platform\PlatformNotificationController;
use App\Http\Controllers\Platform\PlatformDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAppointmentBookingController;
use App\Http\Controllers\ReceivablesReportController;
use App\Http\Controllers\ReceptionDashboardController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Saas\BillingController;
use App\Http\Controllers\Saas\PricingController;
use App\Http\Controllers\Saas\RegisterClinicController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaffCompensationProfileController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffPaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use App\Models\Plan;
use App\Models\User;
use App\Support\ClinicPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    $plans = Plan::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->limit(3)
        ->get();

    return view('welcome', compact('plans'));
});

Route::middleware('guest')->group(function () {
    Route::get('/register-clinic', [RegisterClinicController::class, 'create'])->name('saas.register-clinic.create');
    Route::post('/register-clinic', [RegisterClinicController::class, 'store'])->name('saas.register-clinic.store');
});

Route::get('/pricing', PricingController::class)->name('saas.pricing');
Route::get('/pricing/brochure.pdf', \App\Http\Controllers\Saas\PricingBrochureController::class)->name('saas.pricing.brochure');

Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:6,1');
Route::get('/my/{token}', [PatientPortalController::class, 'show'])->name('portal.patient.show');
Route::get('/og-image.png', OgImageController::class)->name('og.image');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    $supportedLocales = config('app.supported_locales', ['ar', 'en']);

    abort_unless(in_array($locale, $supportedLocales, true), 404);

    session(['locale' => $locale]);

    if (auth()->check() && Schema::hasColumn('users', 'locale')) {
        /** @var User $user */
        $user = auth()->user();
        $user->forceFill(['locale' => $locale])->save();
    }

    // JSON for fetch/XHR: rely on Accept: application/json (not ajax()), because some
    // stacks strip or omit X-Requested-With and then back() breaks client-side JSON.parse.
    if ($request->wantsJson()) {
        return response()->json([
            'success' => true,
            'locale' => $locale,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    return back();
})->name('locale.switch');

Route::post('/locale/apply', LocaleApplyController::class)->name('locale.apply');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/users/{user}/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');

    Route::get('/billing', [BillingController::class, 'show'])->name('saas.billing');
    Route::get('/billing/pdf', [BillingController::class, 'pdfStatement'])->name('saas.billing.pdf');
    Route::post('/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('saas.checkout');
    Route::post('/billing/cancel', [BillingController::class, 'cancelSubscription'])->name('saas.billing.cancel');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('saas.billing.portal');
});

Route::middleware(['auth', 'verified', 'platform.owner'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('plans', [AdminPlanController::class, 'index'])->name('plans.index');
    Route::get('plans/data', [AdminPlanController::class, 'data'])->name('plans.data');
    Route::post('plans', [AdminPlanController::class, 'store'])->name('plans.store');
    Route::put('plans/{plan}', [AdminPlanController::class, 'update'])->name('plans.update');
    Route::delete('plans/{plan}', [AdminPlanController::class, 'destroy'])->name('plans.destroy');
    Route::post('plans/{plan}/toggle-active', [AdminPlanController::class, 'toggleActive'])->name('plans.toggle-active');
});

Route::middleware(['auth', 'verified', 'platform.owner'])->prefix('platform')->name('platform.')->group(function () {
    Route::redirect('/', '/platform/dashboard');
    Route::get('/dashboard', PlatformDashboardController::class)->name('dashboard');

    Route::get('/notifications', [PlatformNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [PlatformNotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::post('/notifications/{appNotification}/read', [PlatformNotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('/clinics/export', [PlatformExportController::class, 'clinics'])->name('clinics.export');
    Route::get('/payments/export', [PlatformExportController::class, 'payments'])->name('payments.export');
    Route::get('/clinics', [PlatformClinicController::class, 'index'])->name('clinics.index');
    Route::get('/clinics/{clinic}/payments/export', [PlatformExportController::class, 'clinicPayments'])->name('clinics.payments.export');
    Route::get('/clinics/{clinic}/subscription', [PlatformClinicController::class, 'subscription'])->name('clinics.subscription');
    Route::get('/clinics/{clinic}/edit', [PlatformClinicController::class, 'edit'])->name('clinics.edit');
    Route::put('/clinics/{clinic}', [PlatformClinicController::class, 'update'])->name('clinics.update');
    Route::post('/clinics/{clinic}/activate', [PlatformClinicController::class, 'activate'])->name('clinics.activate');
    Route::post('/clinics/{clinic}/suspend', [PlatformClinicController::class, 'suspend'])->name('clinics.suspend');
    Route::post('/clinics/{clinic}/record-payment', [PlatformClinicController::class, 'recordPayment'])->name('clinics.record-payment');
});

Route::middleware(['auth', 'verified', 'ensure.user.has.clinic', 'prevent.platform.owner.from.clinic', 'subscription.active'])->group(function () {
    Route::get('/exports/download/{path}', ExportDownloadController::class)
        ->where('path', '[A-Za-z0-9._-]+')
        ->name('exports.download');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:'.ClinicPermissions::VIEW_DASHBOARD)
        ->name('dashboard');
    Route::post('/dashboard/onboarding-dismiss', [DashboardController::class, 'dismissOnboarding'])
        ->middleware('permission:'.ClinicPermissions::VIEW_DASHBOARD)
        ->name('dashboard.onboarding-dismiss');

    Route::get('/reception', ReceptionDashboardController::class)
        ->middleware('permission:'.ClinicPermissions::MANAGE_VISITS)
        ->name('reception.dashboard');

    Route::get('/clinical', ClinicalDashboardController::class)
        ->middleware('role_or_permission:doctor|admin')
        ->name('clinical.dashboard');

    Route::middleware('permission:'.ClinicPermissions::MANAGE_PATIENTS)->group(function () {
        Route::get('patients/export', [ExcelExportController::class, 'patients'])->name('patients.export');
        Route::resource('patients', PatientController::class);
    });

    Route::middleware(['can:viewProfile,patient'])->group(function () {
        Route::get('patients/{patient}/qr-card', [PatientEmrController::class, 'qrCard'])->name('patients.qr-card');
        Route::post('patients/{patient}/portal-link', [PatientEmrController::class, 'issuePortalLink'])->name('patients.portal-link');
        Route::get('patients/{patient}/profile', [PatientAccountController::class, 'profile'])->name('patients.profile');
        Route::get('patients/{patient}/profile/print', [PatientAccountController::class, 'profilePrint'])->name('patients.profile.print');
        Route::get('patients/{patient}/profile/pdf', [PatientAccountController::class, 'profilePdf'])->name('patients.profile.pdf');
        Route::get('patients/{patient}/statement', [PatientAccountController::class, 'statement'])->name('patients.statement');
        Route::get('patients/{patient}/statement/print', [PatientAccountController::class, 'statementPrint'])->name('patients.statement.print');
        Route::get('patients/{patient}/statement/pdf', [PatientAccountController::class, 'statementPdf'])->name('patients.statement.pdf');
        Route::get('patients/{patient}/statement/excel', [PatientAccountController::class, 'statementExcel'])->name('patients.statement.excel');
    });

    Route::middleware('permission:'.ClinicPermissions::MANAGE_PATIENTS)->group(function () {
        Route::post('patients/{patient}/clinical-records', [PatientEmrController::class, 'storeClinicalRecord'])->name('patients.clinical.store');
        Route::delete('patients/{patient}/clinical-records/{record}', [PatientEmrController::class, 'destroyClinicalRecord'])->name('patients.clinical.destroy');
    });

    Route::middleware('permission:'.ClinicPermissions::MANAGE_ATTACHMENTS)->group(function () {
        Route::post('patients/{patient}/attachments', [AttachmentController::class, 'storeForPatient'])->name('patients.attachments.store');
        Route::post('visits/{visit}/attachments', [AttachmentController::class, 'storeForVisit'])->name('visits.attachments.store');
        Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');
    });

    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::get('attachments/{attachment}/preview', [AttachmentController::class, 'preview'])->name('attachments.preview');

    Route::get('lookup/patient/{token}', PatientLookupController::class)->name('patients.lookup');

    Route::middleware('permission:'.ClinicPermissions::MANAGE_DOCTORS)->group(function () {
        Route::resource('doctors', DoctorController::class);
        Route::get('doctors/{doctor}/schedules', [DoctorScheduleController::class, 'index'])->name('doctors.schedules.index');
        Route::post('doctors/{doctor}/schedules', [DoctorScheduleController::class, 'store'])->name('doctors.schedules.store');
        Route::put('doctors/{doctor}/schedules/{schedule}', [DoctorScheduleController::class, 'update'])->name('doctors.schedules.update');
        Route::delete('doctors/{doctor}/schedules/{schedule}', [DoctorScheduleController::class, 'destroy'])->name('doctors.schedules.destroy');
    });

    Route::get('doctors/{doctor}/financial-summary', [DoctorController::class, 'financialSummary'])
        ->middleware('permission:'.ClinicPermissions::VIEW_DOCTOR_EARNINGS.'|'.ClinicPermissions::MANAGE_DOCTOR_EARNINGS)
        ->name('doctors.financial-summary');

    Route::middleware('permission:'.ClinicPermissions::MANAGE_APPOINTMENTS)->group(function () {
        Route::get('appointments/export', [ExcelExportController::class, 'appointments'])->name('appointments.export');
        Route::get('appointments/calendar', [AppointmentCalendarController::class, 'index'])->name('appointments.calendar');
        Route::get('appointments/calendar/events', [AppointmentCalendarController::class, 'events'])->name('appointments.calendar.events');
        Route::patch('appointments/{appointment}/calendar/reschedule', [AppointmentCalendarController::class, 'reschedule'])->name('appointments.calendar.reschedule');
        Route::patch('appointments/{appointment}/calendar/status', [AppointmentCalendarController::class, 'updateStatus'])->name('appointments.calendar.status');
        Route::post('appointments/{appointment}/check-in', [AppointmentCalendarController::class, 'checkIn'])->name('appointments.calendar.check-in');
        Route::get('appointments/slots', [AppointmentSlotController::class, 'index'])->name('appointments.slots');
        Route::resource('appointments', AppointmentController::class);
    });

    Route::middleware('permission:'.ClinicPermissions::MANAGE_VISITS)->group(function () {
        Route::get('visits/export', [ExcelExportController::class, 'visits'])->name('visits.export');
        Route::resource('visits', VisitController::class);
    });

    Route::middleware('permission:'.ClinicPermissions::MANAGE_INVOICES)->group(function () {
        Route::get('invoices/export', [ExcelExportController::class, 'invoices'])->name('invoices.export');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'printInvoice'])->name('invoices.print');
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdfInvoice'])->name('invoices.pdf');
        Route::resource('invoices', InvoiceController::class);
        Route::resource('services', ServiceController::class)->except(['show', 'destroy']);
    });

    Route::middleware('permission:'.ClinicPermissions::MANAGE_EXPENSE_CATEGORIES)->group(function () {
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show', 'destroy']);
    });

    Route::middleware('permission:'.ClinicPermissions::MANAGE_EXPENSES)->group(function () {
        Route::get('expenses/{expense}/print', [ExpenseController::class, 'printExpense'])->name('expenses.print');
        Route::get('expenses/{expense}/pdf', [ExpenseController::class, 'pdfExpense'])->name('expenses.pdf');
        Route::get('expenses/{expense}/payments/{expense_payment}/print', [ExpenseController::class, 'printPayment'])->name('expenses.payment-print');
        Route::get('expenses/{expense}/payments/{expense_payment}/pdf', [ExpenseController::class, 'pdfPayment'])->name('expenses.payment-pdf');
        Route::post('expenses/{expense}/payments', [ExpensePaymentController::class, 'store'])->name('expenses.payments.store');
        Route::delete('expenses/{expense}/payments/{expense_payment}', [ExpensePaymentController::class, 'destroy'])->name('expenses.payments.destroy');
        Route::resource('expenses', ExpenseController::class);
    });

    Route::middleware('permission:'.ClinicPermissions::VIEW_INVENTORY.'|'.ClinicPermissions::MANAGE_INVENTORY)->group(function () {
        Route::get('/inventory', InventoryDashboardController::class)->name('inventory.dashboard');
        Route::get('/inventory/reports', [InventoryReportController::class, 'index'])->name('inventory.reports.index');
        Route::get('/inventory/movements', [InventoryStockMovementController::class, 'index'])->name('inventory.movements.index');
        Route::get('/inventory/suppliers', [InventorySupplierController::class, 'index'])->name('inventory.suppliers.index');
        Route::get('/inventory/purchases', [InventoryPurchaseController::class, 'index'])->name('inventory.purchases.index');
        Route::resource('inventory/templates', InventoryProcedureTemplateController::class)
            ->except(['show', 'destroy'])
            ->names('inventory.templates');
        Route::resource('inventory/items', InventoryItemController::class)
            ->parameters(['items' => 'item'])
            ->names('inventory.items')
            ->except(['show']);
    });

    Route::middleware('permission:'.ClinicPermissions::MANAGE_INVENTORY)->group(function () {
        Route::get('/inventory/movements/create', [InventoryStockMovementController::class, 'create'])->name('inventory.movements.create');
        Route::post('/inventory/movements', [InventoryStockMovementController::class, 'store'])->name('inventory.movements.store');
        Route::post('/inventory/suppliers', [InventorySupplierController::class, 'store'])->name('inventory.suppliers.store');
        Route::get('/inventory/purchases/create', [InventoryPurchaseController::class, 'create'])->name('inventory.purchases.create');
        Route::post('/inventory/purchases', [InventoryPurchaseController::class, 'store'])->name('inventory.purchases.store');
    });

    Route::middleware('permission:'.ClinicPermissions::MANAGE_STAFF)->group(function () {
        Route::resource('staff', StaffController::class)->except(['show']);
    });

    Route::middleware('permission:'.ClinicPermissions::MANAGE_STAFF_PAYROLL)->group(function () {
        Route::resource('staff-compensation-profiles', StaffCompensationProfileController::class)->except(['show', 'destroy']);
    });

    Route::middleware('permission:'.ClinicPermissions::MANAGE_PAYROLL)->group(function () {
        Route::get('payroll-runs', [PayrollRunController::class, 'index'])->name('payroll-runs.index');
        Route::post('payroll-runs', [PayrollRunController::class, 'store'])->name('payroll-runs.store');
        Route::post('payroll-runs/{payroll_run}/generate', [PayrollRunController::class, 'generate'])->name('payroll-runs.generate');
        Route::resource('staff-payments', StaffPaymentController::class)->except(['show']);
    });

    Route::middleware('permission:'.ClinicPermissions::VIEW_DOCTOR_EARNINGS.'|'.ClinicPermissions::MANAGE_DOCTOR_EARNINGS)->group(function () {
        Route::get('doctor-earnings', [DoctorEarningController::class, 'index'])->name('doctor-earnings.index');
        Route::get('doctor-earnings/export', [DoctorEarningController::class, 'exportCsv'])->name('doctor-earnings.export');
    });

    Route::post('doctor-earnings/{doctor_earning}/pay', [DoctorEarningController::class, 'markAsPaid'])
        ->middleware('permission:'.ClinicPermissions::MANAGE_DOCTOR_EARNINGS)
        ->name('doctor-earnings.pay');

    Route::post('doctor-earnings/batch-pay', [DoctorEarningController::class, 'batchPay'])
        ->middleware('permission:'.ClinicPermissions::MANAGE_DOCTOR_EARNINGS)
        ->name('doctor-earnings.batch-pay');

    Route::post('payments', [PaymentController::class, 'store'])
        ->middleware('permission:'.ClinicPermissions::MANAGE_PAYMENTS)
        ->name('payments.store');

    Route::middleware('permission:'.ClinicPermissions::VIEW_REPORTS)->group(function () {
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/print', [ReportsController::class, 'printReport'])->name('reports.print');
        Route::get('/reports/pdf', [ReportsController::class, 'pdfReport'])->name('reports.pdf');
        Route::get('/reports/excel', [ReportsController::class, 'exportExcel'])->name('reports.excel');
        Route::get('/reports/receivables', [ReceivablesReportController::class, 'index'])->name('reports.receivables');
        Route::get('/reports/receivables/pdf', [ReceivablesReportController::class, 'pdf'])->name('reports.receivables.pdf');
        Route::get('/reports/receivables/excel', [ReceivablesReportController::class, 'excel'])->name('reports.receivables.excel');
    });

    Route::get('/reports/doctors/{doctor}', [DoctorReportController::class, 'show'])->name('reports.doctors.show');
    Route::get('/reports/doctors/{doctor}/pdf', [DoctorReportController::class, 'pdf'])->name('reports.doctors.pdf');
    Route::get('/reports/doctors/{doctor}/excel', [DoctorReportController::class, 'excel'])->name('reports.doctors.excel');

    Route::middleware('permission:'.ClinicPermissions::MANAGE_USERS)->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::get('/clinic-settings/{setting}/logo', [ClinicSettingsController::class, 'logo'])
        ->name('settings.logo');

    Route::middleware('permission:'.ClinicPermissions::MANAGE_SETTINGS)->group(function () {
        Route::get('/settings', [ClinicSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [ClinicSettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/public-booking-link', [ClinicSettingsController::class, 'generatePublicBookingLink'])
            ->name('settings.public-booking-link');
    });

    Route::get('/audit-logs', [AuditLogController::class, 'index'])
        ->middleware('permission:'.ClinicPermissions::VIEW_AUDIT_LOGS)
        ->name('audit-logs.index');

    Route::middleware('permission:'.ClinicPermissions::MANAGE_BACKUPS)->group(function () {
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('/backups/{filename}/download', [BackupController::class, 'download'])
            ->where('filename', 'clinic_backup_[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{2}_[0-9]{2}_[0-9]{2}\.(sql|sqlite)')
            ->name('backups.download');
        Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])
            ->where('filename', 'clinic_backup_[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{2}_[0-9]{2}_[0-9]{2}\.(sql|sqlite)')
            ->name('backups.destroy');
    });

    Route::middleware('permission:'.ClinicPermissions::VIEW_NOTIFICATIONS)->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
        Route::post('/notifications/{appNotification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    });

    Route::prefix('chat')->name('chat.')->middleware('permission:'.ClinicPermissions::VIEW_DASHBOARD.'|'.ClinicPermissions::MANAGE_VISITS.'|'.ClinicPermissions::MANAGE_STAFF)->group(function () {
        Route::get('/users', [InternalChatController::class, 'users'])->name('users');
        Route::get('/general', [InternalChatController::class, 'generalMessages'])->name('general');
        Route::delete('/general/conversation', [InternalChatController::class, 'clearGeneralConversation'])->name('general.conversation.clear');
        Route::post('/general/send', [InternalChatController::class, 'sendGeneral'])->name('general.send');
        Route::post('/private/send', [InternalChatController::class, 'sendPrivate'])->name('private.send');
        Route::get('/private/{user}', [InternalChatController::class, 'privateMessages'])
            ->whereNumber('user')
            ->name('private.messages');
        Route::post('/private/{user}/read', [InternalChatController::class, 'markPrivateAsRead'])
            ->whereNumber('user')
            ->name('private.read');
        Route::delete('/private/{user}/conversation', [InternalChatController::class, 'clearPrivateConversation'])
            ->whereNumber('user')
            ->name('private.conversation.clear');
        Route::get('/unread-counts', [InternalChatController::class, 'unreadCounts'])->name('unread-counts');
        Route::patch('/messages/{chatMessage}', [InternalChatController::class, 'updateMessage'])->name('messages.update');
        Route::delete('/messages/{chatMessage}', [InternalChatController::class, 'destroyMessage'])->name('messages.destroy');
    });
});

Route::prefix('book')->name('booking.public.')->group(function () {
    Route::get('{token}', [PublicAppointmentBookingController::class, 'show'])->name('show');
    Route::get('{token}/slots', [PublicAppointmentBookingController::class, 'slots'])->name('slots');
    Route::post('{token}', [PublicAppointmentBookingController::class, 'store'])->name('store');
    Route::get('{token}/confirmation/{appointment}', [PublicAppointmentBookingController::class, 'confirmation'])
        ->whereNumber('appointment')
        ->name('confirmation');
});

require __DIR__.'/security.php';
require __DIR__.'/auth.php';
