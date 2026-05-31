# Scheduling architecture (Phase 2)

## Overview

The appointment module is extended into a scheduling ecosystem: doctor availability, slot generation, conflict prevention, FullCalendar UI, visit check-in, reminder plumbing, and tokenized public booking—without replacing existing appointment records or list CRUD.

## Database schema

### `clinic_settings` (columns)

| Column | Purpose |
|--------|---------|
| `scheduling_slot_minutes` | Default slot length (default 15) |
| `scheduling_day_start` / `scheduling_day_end` | Clinic working window |
| `scheduling_buffer_minutes` | Gap between appointments |
| `scheduling_allow_overbooking` | Allow double-booking when true |
| `scheduling_reminders_enabled` | Master switch for reminder rows |

### New tables

- **`doctor_schedules`** — recurring weekly availability per doctor (`day_of_week`, `start_time`, `end_time`)
- **`doctor_schedule_breaks`** — breaks within a schedule
- **`doctor_unavailable_dates`** — vacations, blocked days, partial blocks
- **`appointment_reminders`** — queued reminder jobs (email / in-app; SMS/WhatsApp-ready `channel`)
- **`appointment_booking_tokens`** — hashed tokens for public self-booking links

### `appointments` (columns)

| Column | Purpose |
|--------|---------|
| `duration_minutes` | Slot length / resize |
| `checked_in_at` | Reception check-in timestamp |
| `visit_id` | Linked visit after check-in |
| `booking_source` | `staff` or `online` |
| `public_booking_token` | Plain token reference for online bookings |
| `status` | Widened to `VARCHAR(32)` for lifecycle values |

## Status lifecycle

`scheduled` → `confirmed` → `checked_in` → `in_progress` → `completed`  
Branches: `cancelled`, `no_show` from early states.

Blocking statuses (for conflicts): `scheduled`, `confirmed`, `checked_in`, `in_progress`.

## Services

| Service | Role |
|---------|------|
| `AppointmentConflictService` | Overlaps, clinic hours, doctor schedule/breaks, unavailable dates, buffer |
| `AppointmentSlotService` | Generates `{ start, end, available }` slots for a doctor/date |
| `AppointmentCalendarService` | FullCalendar JSON + drag/resize `reschedule()` |
| `AppointmentLifecycleService` | Status transitions, `checkIn()` → `Visit` (waiting queue), `syncFromVisit()` |
| `AppointmentReminderPlanner` | Creates reminder rows; dispatches `SendAppointmentReminderJob` |

## Slot engine

1. Resolve clinic window from `SchedulingSettings` (clinic_settings + `config/scheduling.php` defaults).
2. Intersect with active `doctor_schedules` for the ISO weekday.
3. Step by `scheduling_slot_minutes`; each candidate calls `assertCanBook()` (conflict service).
4. Returns slots with `available: true|false` for instant UI feedback.

## Performance

- Calendar events: single query per range with `patient`/`doctor` eager load; index on `(doctor_id, appointment_date)`.
- Slot API: O(slots × conflict checks); acceptable for reception (one doctor/day). Cache per doctor/date if needed later.
- Reminders: `appointment_reminders` indexed by `(scheduled_for, status)`; jobs are queued.

## Routes

| Route | Purpose |
|-------|---------|
| `appointments/calendar` | FullCalendar UI |
| `appointments/calendar/events` | JSON feed |
| `appointments/{id}/calendar/reschedule` | Drag/resize |
| `appointments/{id}/calendar/status` | Lifecycle from modal |
| `appointments/{id}/check-in` | Create/link visit |
| `appointments/slots` | Slot picker API |
| `book/{token}` | Public booking (guest) |

## Remaining roadmap

- Doctor schedule CRUD UI (admin/settings)
- SMS/WhatsApp providers wired to `SendAppointmentReminderJob`
- Patient portal: issue booking links from reception (use `PublicAppointmentBookingController::issueToken()`)
- Recurring appointment templates
- Multi-location / room resources
- Calendar resource view (one column per doctor)
- Reception dashboard widget: today’s appointments + one-click check-in

## Tests

`tests/Feature/AppointmentSchedulingTest.php` — conflicts, slot availability, lifecycle transitions, calendar events JSON.
