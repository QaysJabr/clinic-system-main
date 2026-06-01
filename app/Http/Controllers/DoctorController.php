<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Staff;
use App\Services\StaffDoctorSyncService;
use App\Support\AuditLogger;
use App\Support\DoctorFinancialSummary;
use App\Support\Queries\DoctorListQuery;
use App\Support\TenantValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    private const AUDIT_FIELDS = [
        'staff_id',
        'full_name',
        'specialty',
        'phone',
        'email',
        'license_number',
        'room_number',
        'status',
        'notes',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $scope = Doctor::query();

        $doctorStats = [
            'total' => (clone $scope)->count(),
            'active' => (clone $scope)->where('status', 'active')->count(),
            'inactive' => (clone $scope)->where('status', 'inactive')->count(),
        ];

        $doctors = DoctorListQuery::apply(clone $scope, $request)
            ->orderBy('full_name')
            ->paginate(15)
            ->appends($request->query());

        $pageTitle = __('doctors.page_title_index');

        if ($request->ajax()) {
            return view('doctors.partials.content', compact('doctors', 'pageTitle', 'doctorStats'));
        }

        return view('doctors.index', compact('doctors', 'pageTitle', 'doctorStats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $linkedStaff = Staff::query()
            ->where('role_type', 'doctor')
            ->whereDoesntHave('doctor')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'phone', 'email', 'status']);

        $pageTitle = __('doctors.page_title_create');
        $linkOnly = true;

        if ($request->ajax()) {
            return view('doctors.partials.create', compact('linkedStaff', 'pageTitle', 'linkOnly'));
        }

        return view('doctors.create', compact('linkedStaff', 'pageTitle', 'linkOnly'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, StaffDoctorSyncService $staffDoctorSync)
    {
        if (! $request->filled('staff_id')) {
            return redirect()
                ->route('staff.create', ['role' => 'doctor'])
                ->with('info', __('doctors.redirect_add_via_staff'));
        }

        $clinicId = TenantValidation::clinicIdForRules();
        $staff = Staff::query()
            ->where('role_type', 'doctor')
            ->findOrFail($request->integer('staff_id'));

        if (Doctor::query()->where('staff_id', $staff->id)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['staff_id' => __('doctors.staff_already_has_doctor')]);
        }

        $request->validate([
            'staff_id' => ['required', 'integer', Rule::exists('staff', 'id')->where(fn ($q) => $q->where('role_type', 'doctor')->where('clinic_id', $clinicId))],
            'specialty' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', Rule::unique('doctors', 'license_number')->where(fn ($q) => $q->where('clinic_id', $clinicId))],
            'room_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $doctor = $staffDoctorSync->syncDoctorForStaff($staff, $staffDoctorSync->doctorFieldsFromRequest($request));

        AuditLogger::log(
            'create',
            'doctors',
            $doctor->id,
            __('doctors.audit_create_body', ['name' => $doctor->full_name]),
            null,
            $doctor->only(self::AUDIT_FIELDS)
        );

        return redirect()->route('doctors.index')->with('success', __('doctors.flash_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Doctor dues summary from doctor_earnings (pending / paid to doctor).
     */
    public function financialSummary(Doctor $doctor): JsonResponse
    {
        $summary = DoctorFinancialSummary::forDoctor((int) $doctor->id);

        return response()->json([
            'doctor_id' => $doctor->id,
            'doctor_name' => $doctor->full_name,
            ...$summary,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $doctor = Doctor::with('staff')->findOrFail($id);
        $linkedStaff = Staff::query()
            ->where('role_type', 'doctor')
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $pageTitle = __('doctors.page_title_edit');
        $linkOnly = false;

        if ($request->ajax()) {
            return view('doctors.partials.edit', compact('doctor', 'linkedStaff', 'pageTitle', 'linkOnly'));
        }

        return view('doctors.edit', compact('doctor', 'linkedStaff', 'pageTitle', 'linkOnly'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, StaffDoctorSyncService $staffDoctorSync)
    {
        $doctor = Doctor::with('staff')->findOrFail($id);

        if ($doctor->staff_id) {
            $request->validate([
                'specialty' => ['nullable', 'string', 'max:255'],
                'license_number' => [
                    'nullable',
                    'string',
                    Rule::unique('doctors', 'license_number')
                        ->where(fn ($q) => $q->where('clinic_id', $doctor->clinic_id))
                        ->ignore($doctor->id),
                ],
                'room_number' => ['nullable', 'string', 'max:50'],
                'notes' => ['nullable', 'string'],
            ]);

            $old = $doctor->only(self::AUDIT_FIELDS);
            $staff = $doctor->staff ?? Staff::query()->findOrFail($doctor->staff_id);
            $doctor = $staffDoctorSync->syncDoctorForStaff($staff, $staffDoctorSync->doctorFieldsFromRequest($request));
        } else {
            $request->validate([
                'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')->where(fn ($q) => $q->where('role_type', 'doctor')->where('clinic_id', $doctor->clinic_id))],
                'full_name' => 'required|string|max:255',
                'specialty' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => ['nullable', 'email', Rule::unique('doctors', 'email')->ignore($doctor->id)->where(fn ($q) => $q->where('clinic_id', $doctor->clinic_id))],
                'license_number' => ['nullable', 'string', Rule::unique('doctors', 'license_number')->ignore($doctor->id)->where(fn ($q) => $q->where('clinic_id', $doctor->clinic_id))],
                'room_number' => 'nullable|string|max:50',
                'status' => 'required|in:active,inactive',
                'notes' => 'nullable|string',
            ]);

            $old = $doctor->only(self::AUDIT_FIELDS);
            $doctor->update($this->doctorPayloadFromRequest($request));
        }

        AuditLogger::log(
            'update',
            'doctors',
            $doctor->id,
            __('doctors.audit_update_body', ['name' => $doctor->full_name]),
            $old,
            $doctor->fresh()->only(self::AUDIT_FIELDS)
        );

        return redirect()->route('doctors.index')->with('success', __('doctors.flash_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $doctor = Doctor::findOrFail($id);
        $old = $doctor->only(self::AUDIT_FIELDS);
        $did = $doctor->id;
        $name = $doctor->full_name;
        $doctor->delete();

        AuditLogger::log(
            'delete',
            'doctors',
            $did,
            __('doctors.audit_delete_body', ['name' => $name]),
            $old,
            null
        );

        return redirect()->route('doctors.index')->with('success', __('doctors.flash_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function doctorPayloadFromRequest(Request $request): array
    {
        $data = $request->only(self::AUDIT_FIELDS);
        $data['staff_id'] = $request->filled('staff_id') ? $request->integer('staff_id') : null;

        return $data;
    }
}
