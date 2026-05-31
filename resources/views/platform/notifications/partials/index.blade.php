<div class="w-full max-w-[1400px] mx-auto" dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}">
    <a href="{{ route('platform.dashboard') }}" data-spa class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-[#1F7A8C] no-underline hover:text-[#0F4C81] dark:text-[#5EEAD4]">
        ← {{ __('navigation.back_to_dashboard') }}
    </a>
</div>
@include('notifications.partials.index', [
    'notifications' => $notifications,
    'pageTitle' => $pageTitle,
    'notificationsTitle' => __('platform.notifications_title'),
    'notificationsSubtitle' => __('platform.notifications_subtitle'),
    'notificationsReadAllRoute' => 'platform.notifications.readAll',
    'notificationsReadRoute' => 'platform.notifications.read',
])
