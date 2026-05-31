<?php

namespace App\Providers;

use App\Listeners\RecordStripeWebhookEvent;
use App\Models\AppNotification;
use App\Models\Appointment;
use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\ChatMessage;
use App\Models\Clinic;
use App\Models\ClinicSetting;
use App\Models\Doctor;
use App\Models\DoctorEarning;
use App\Models\DoctorSchedule;
use App\Models\DoctorUnavailableDate;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpensePayment;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryProcedureTemplate;
use App\Models\InventoryProcedureTemplateItem;
use App\Models\InventoryPurchase;
use App\Models\InventoryPurchaseLine;
use App\Models\InventoryStockMovement;
use App\Models\InventorySupplier;
use App\Models\InventoryUnit;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\PatientClinicalRecord;
use App\Models\PatientDiagnosis;
use App\Models\PatientLookupToken;
use App\Models\Payment;
use App\Models\PayrollRun;
use App\Models\Scopes\TenantScope;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffCompensationProfile;
use App\Models\StaffPayment;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitPrescription;
use App\Models\VisitProcedure;
use App\Observers\AppNotificationObserver;
use App\Observers\AppointmentObserver;
use App\Observers\CashierSubscriptionObserver;
use App\Observers\DoctorEarningObserver;
use App\Observers\ExpenseObserver;
use App\Observers\InvoiceObserver;
use App\Observers\PatientObserver;
use App\Observers\PaymentObserver;
use App\Observers\StaffPaymentObserver;
use App\Observers\VisitObserver;
use App\Policies\AppointmentPolicy;
use App\Policies\AttachmentPolicy;
use App\Policies\DoctorSchedulePolicy;
use App\Policies\ExpensePolicy;
use App\Policies\InventoryItemPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PatientPolicy;
use App\Policies\StaffPolicy;
use App\Policies\UserPolicy;
use App\Policies\VisitPolicy;
use App\Services\Reminders\InAppReminderChannelSender;
use App\Services\Reminders\LaravelMailAppointmentReminderChannelSender;
use App\Services\Reminders\ReminderChannelRegistry;
use App\Services\Reminders\SmsAppointmentReminderChannelSender;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;
use Laravel\Cashier\Subscription;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Cashier::$customerModel = Clinic::class;

        $this->app->singleton(ReminderChannelRegistry::class, function ($app): ReminderChannelRegistry {
            $registry = new ReminderChannelRegistry;
            $registry->register($app->make(InAppReminderChannelSender::class));
            $registry->register($app->make(LaravelMailAppointmentReminderChannelSender::class));
            $registry->register($app->make(SmsAppointmentReminderChannelSender::class));

            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerTenantGlobalScopes();

        Gate::policy(Visit::class, VisitPolicy::class);
        Gate::policy(Patient::class, PatientPolicy::class);
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(InventoryItem::class, InventoryItemPolicy::class);
        Gate::policy(Staff::class, StaffPolicy::class);
        Gate::policy(DoctorSchedule::class, DoctorSchedulePolicy::class);

        Invoice::observe(InvoiceObserver::class);
        Payment::observe(PaymentObserver::class);
        DoctorEarning::observe(DoctorEarningObserver::class);
        StaffPayment::observe(StaffPaymentObserver::class);
        AppNotification::observe(AppNotificationObserver::class);
        Appointment::observe(AppointmentObserver::class);
        Visit::observe(VisitObserver::class);
        Patient::observe(PatientObserver::class);
        Expense::observe(ExpenseObserver::class);
        Subscription::observe(CashierSubscriptionObserver::class);

        Event::listen(WebhookHandled::class, RecordStripeWebhookEvent::class);

        $this->ensureDompdfFontCacheDirectory();
        $this->ensurePdfArabicFont();

        Blade::directive('safeDate', function (string $expression): string {
            return "<?php echo optional(\\Illuminate\\Support\\Carbon::make({$expression}))->format('d/m/Y') ?? '—'; ?>";
        });

        /** DomPDF only: reshape Arabic; browser/Chrome export uses logical order. */
        Blade::directive('pdfStr', function (string $expression): string {
            return "<?php echo e(((\$clinicPdfExport ?? false) || (\\Illuminate\\Support\\Facades\\View::shared('clinicPdfExport') ?? false)) ? \\App\\Support\\PdfArabic::glyphs({$expression}) : ({$expression})); ?>";
        });

        View::composer('layouts.platform', function ($view): void {
            if (! auth()->check() || ! auth()->user()->hasRole('super_admin')) {
                $view->with([
                    'unreadNotificationsCount' => 0,
                    'headerNotifications' => collect(),
                    'headerNotificationsIndexRoute' => null,
                    'headerNotificationsReadAllRoute' => null,
                ]);

                return;
            }

            $uid = (int) auth()->id();
            $base = AppNotification::query()->forUser($uid)->platformOnly();

            $view->with([
                'unreadNotificationsCount' => (clone $base)->unread()->count(),
                'headerNotifications' => (clone $base)
                    ->select(['id', 'type', 'title', 'message', 'created_at', 'is_read'])
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get(),
                'headerNotificationsIndexRoute' => route('platform.notifications.index'),
                'headerNotificationsReadAllRoute' => route('platform.notifications.readAll'),
            ]);
        });

        View::composer('layouts.app', function ($view): void {
            if (! auth()->check()) {
                $view->with([
                    'unreadNotificationsCount' => 0,
                    'headerNotifications' => collect(),
                    'headerNotificationsIndexRoute' => null,
                    'headerNotificationsReadAllRoute' => null,
                ]);

                return;
            }

            if (! auth()->user()->can('view notifications')) {
                $view->with([
                    'unreadNotificationsCount' => 0,
                    'headerNotifications' => collect(),
                    'headerNotificationsIndexRoute' => null,
                    'headerNotificationsReadAllRoute' => null,
                ]);

                return;
            }

            $uid = (int) auth()->id();
            $base = AppNotification::query()->forUser($uid)->clinicOnly();

            $view->with([
                'unreadNotificationsCount' => (clone $base)->unread()->count(),
                'headerNotifications' => (clone $base)
                    ->select(['id', 'type', 'title', 'message', 'created_at', 'is_read'])
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get(),
                'headerNotificationsIndexRoute' => route('notifications.index'),
                'headerNotificationsReadAllRoute' => route('notifications.readAll'),
            ]);
        });
    }

    /**
     * Register {@see TenantScope} on all clinic-scoped models (trait boot callbacks are unreliable for global scopes).
     */
    private function registerTenantGlobalScopes(): void
    {
        foreach ([
            Patient::class,
            Doctor::class,
            Staff::class,
            Appointment::class,
            Visit::class,
            Invoice::class,
            InvoiceItem::class,
            Payment::class,
            DoctorEarning::class,
            ClinicSetting::class,
            Attachment::class,
            Expense::class,
            ExpensePayment::class,
            ExpenseCategory::class,
            Service::class,
            VisitProcedure::class,
            VisitPrescription::class,
            PayrollRun::class,
            StaffPayment::class,
            StaffCompensationProfile::class,
            AppNotification::class,
            AuditLog::class,
            ChatMessage::class,
            DoctorSchedule::class,
            DoctorUnavailableDate::class,
            PatientClinicalRecord::class,
            PatientDiagnosis::class,
            PatientLookupToken::class,
            InventoryCategory::class,
            InventoryUnit::class,
            InventorySupplier::class,
            InventoryItem::class,
            InventoryProcedureTemplate::class,
            InventoryProcedureTemplateItem::class,
            InventoryPurchase::class,
            InventoryPurchaseLine::class,
            InventoryStockMovement::class,
        ] as $modelClass) {
            $modelClass::addGlobalScope(new TenantScope);
        }
    }

    /**
     * DomPDF يخزّن مقاييس الخط (.ufm) في font_dir — يجب أن يكون المجلد موجوداً وقابلاً للكتابة.
     *
     * @see config/dompdf.php → options.font_dir
     */
    private function ensureDompdfFontCacheDirectory(): void
    {
        $dir = storage_path('fonts');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * DomPDF يستخدم basePath = public؛ الخط يجب أن يكون تحت public/fonts ليُحمَّل ويُضمَّن بشكل صحيح.
     */
    private function ensurePdfArabicFont(): void
    {
        $publicFont = public_path('fonts/NotoSansArabic-Regular.ttf');
        if (is_file($publicFont)) {
            return;
        }

        $sources = [
            resource_path('fonts/NotoSansArabic-Regular.ttf'),
            // Fallback to the Unicode font bundled with DomPDF.
            base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf'),
        ];

        $source = null;
        foreach ($sources as $candidate) {
            if (is_file($candidate)) {
                $source = $candidate;
                break;
            }
        }
        if (! $source) {
            return;
        }

        if (! is_dir(dirname($publicFont))) {
            mkdir(dirname($publicFont), 0755, true);
        }
        @copy($source, $publicFont);
    }
}
