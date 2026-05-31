<?php

return [
    'default_slot_minutes' => (int) env('SCHEDULING_SLOT_MINUTES', 15),
    'default_day_start' => env('SCHEDULING_DAY_START', '09:00'),
    'default_day_end' => env('SCHEDULING_DAY_END', '17:00'),
    'buffer_minutes' => (int) env('SCHEDULING_BUFFER_MINUTES', 0),
    'allow_overbooking' => (bool) env('SCHEDULING_ALLOW_OVERBOOKING', false),
    'public_booking_token_ttl_hours' => (int) env('SCHEDULING_BOOKING_TOKEN_TTL', 48),
    'public_booking_clinic_token_ttl_hours' => (int) env('SCHEDULING_CLINIC_BOOKING_TOKEN_TTL', 8760),
    'reminder_lead_hours' => [24, 2],
];
