<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorScheduleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, Doctor $doctor): View
    {
        $this->authorize('viewAny', [DoctorSchedule::class, $doctor]);

        $schedules = DoctorSchedule::query()
            ->where('doctor_id', $doctor->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $pageTitle = __('schedules.title', ['doctor' => $doctor->full_name]);

        return view('doctors.schedules.index', compact('doctor', 'schedules', 'pageTitle'));
    }

    public function store(Request $request, Doctor $doctor): RedirectResponse
    {
        $this->authorize('viewAny', [DoctorSchedule::class, $doctor]);

        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        DoctorSchedule::query()->create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $doctor->clinic_id,
            'day_of_week' => (int) $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('doctors.schedules.index', $doctor)
            ->with('success', __('schedules.flash_created'));
    }

    public function update(Request $request, Doctor $doctor, DoctorSchedule $schedule): RedirectResponse
    {
        $this->authorize('manage', $schedule);
        abort_unless((int) $schedule->doctor_id === (int) $doctor->id, 404);

        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $schedule->update([
            'day_of_week' => (int) $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('doctors.schedules.index', $doctor)
            ->with('success', __('schedules.flash_updated'));
    }

    public function destroy(Doctor $doctor, DoctorSchedule $schedule): RedirectResponse
    {
        $this->authorize('manage', $schedule);
        abort_unless((int) $schedule->doctor_id === (int) $doctor->id, 404);

        $schedule->delete();

        return redirect()
            ->route('doctors.schedules.index', $doctor)
            ->with('success', __('schedules.flash_deleted'));
    }
}
