<?php

use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DoctorController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\MetaController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PatientController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\Platform\PlatformClinicController;
use App\Http\Controllers\Api\V1\Platform\PlatformDashboardController;
use App\Http\Controllers\Api\V1\Platform\PlatformExportController;
use App\Http\Controllers\Api\V1\Platform\PlatformNotificationController;
use App\Http\Controllers\Api\V1\Platform\PlatformPlanController;
use App\Http\Controllers\Api\V1\PushTokenController;
use App\Http\Controllers\Api\V1\VisitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile / external API (v1) — Bearer token via Laravel Sanctum
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function (): void {
    Route::get('meta', MetaController::class)->name('api.v1.meta');

    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('api.v1.auth.login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout'])
            ->name('api.v1.auth.logout');
        Route::get('auth/me', [AuthController::class, 'me'])
            ->name('api.v1.auth.me');

        Route::middleware('api.platform')->prefix('platform')->name('api.v1.platform.')->group(function (): void {
            Route::get('dashboard', PlatformDashboardController::class)
                ->name('dashboard');
            Route::get('clinics', [PlatformClinicController::class, 'index'])
                ->name('clinics.index');
            Route::get('clinics/{clinic}', [PlatformClinicController::class, 'show'])
                ->name('clinics.show');
            Route::put('clinics/{clinic}', [PlatformClinicController::class, 'update'])
                ->name('clinics.update');
            Route::post('clinics/{clinic}/activate', [PlatformClinicController::class, 'activate'])
                ->name('clinics.activate');
            Route::post('clinics/{clinic}/suspend', [PlatformClinicController::class, 'suspend'])
                ->name('clinics.suspend');
            Route::post('clinics/{clinic}/record-payment', [PlatformClinicController::class, 'recordPayment'])
                ->name('clinics.record-payment');

            Route::get('plans', [PlatformPlanController::class, 'index'])
                ->name('plans.index');
            Route::post('plans', [PlatformPlanController::class, 'store'])
                ->name('plans.store');
            Route::put('plans/{plan}', [PlatformPlanController::class, 'update'])
                ->name('plans.update');
            Route::delete('plans/{plan}', [PlatformPlanController::class, 'destroy'])
                ->name('plans.destroy');
            Route::post('plans/{plan}/toggle-active', [PlatformPlanController::class, 'toggleActive'])
                ->name('plans.toggle-active');

            Route::get('notifications', [PlatformNotificationController::class, 'index'])
                ->name('notifications.index');
            Route::post('notifications/read-all', [PlatformNotificationController::class, 'markAllAsRead'])
                ->name('notifications.read-all');
            Route::post('notifications/{appNotification}/read', [PlatformNotificationController::class, 'markRead'])
                ->name('notifications.read');

            Route::get('exports/clinics', [PlatformExportController::class, 'clinics'])
                ->name('exports.clinics');
            Route::get('exports/payments', [PlatformExportController::class, 'payments'])
                ->name('exports.payments');
            Route::get('exports/clinics/{clinic}/payments', [PlatformExportController::class, 'clinicPayments'])
                ->name('exports.clinic-payments');
        });

        Route::middleware(['api.clinic', 'subscription.active'])->group(function (): void {
            Route::get('dashboard', DashboardController::class)
                ->name('api.v1.dashboard');

            Route::get('doctors', [DoctorController::class, 'index'])
                ->name('api.v1.doctors.index');

            Route::get('appointments', [AppointmentController::class, 'index'])
                ->name('api.v1.appointments.index');
            Route::post('appointments', [AppointmentController::class, 'store'])
                ->name('api.v1.appointments.store');
            Route::get('appointments/{appointment}', [AppointmentController::class, 'show'])
                ->name('api.v1.appointments.show');
            Route::put('appointments/{appointment}', [AppointmentController::class, 'update'])
                ->name('api.v1.appointments.update');
            Route::patch('appointments/{appointment}', [AppointmentController::class, 'update']);
            Route::post('appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn'])
                ->name('api.v1.appointments.check-in');

            Route::get('visits', [VisitController::class, 'index'])
                ->name('api.v1.visits.index');
            Route::get('visits/{visit}', [VisitController::class, 'show'])
                ->name('api.v1.visits.show');
            Route::patch('visits/{visit}/status', [VisitController::class, 'updateStatus'])
                ->name('api.v1.visits.status');
            Route::put('visits/{visit}', [VisitController::class, 'update'])
                ->name('api.v1.visits.update');
            Route::patch('visits/{visit}', [VisitController::class, 'update']);

            Route::get('patients', [PatientController::class, 'index'])
                ->name('api.v1.patients.index');
            Route::post('patients', [PatientController::class, 'store'])
                ->name('api.v1.patients.store');
            Route::get('patients/{patient}', [PatientController::class, 'show'])
                ->name('api.v1.patients.show');

            Route::get('invoices', [InvoiceController::class, 'index'])
                ->name('api.v1.invoices.index');
            Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])
                ->name('api.v1.invoices.show');
            Route::post('payments', [PaymentController::class, 'store'])
                ->name('api.v1.payments.store');

            Route::get('notifications', [NotificationController::class, 'index'])
                ->name('api.v1.notifications.index');
            Route::post('notifications/{appNotification}/read', [NotificationController::class, 'markRead'])
                ->name('api.v1.notifications.read');

            Route::post('push/register', [PushTokenController::class, 'register'])
                ->name('api.v1.push.register');
            Route::post('push/unregister', [PushTokenController::class, 'unregister'])
                ->name('api.v1.push.unregister');
        });
    });
});
