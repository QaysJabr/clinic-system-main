<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Staff;
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
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $pageTitle = __('doctors.page_title_create');

        if ($request->ajax()) {
            return view('doctors.partials.create', compact('linkedStaff', 'pageTitle'));
        }

        return view('doctors.create', compact('linkedStaff', 'pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $clinicId = TenantValidation::clinicIdForRules();

        $request->validate([
            'staff_id' => ['nullable', 'integer', Rule::exists('staff', 'id')->where(fn ($q) => $q->where('role_type', 'doctor')->where('clinic_id', $clinicId))],
            'full_name' => 'required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => ['nullable', 'email', Rule::unique('doctors', 'email')->where(fn ($q) => $q->where('clinic_id', $clinicId))],
            'license_number' => ['nullable', 'string', Rule::unique('doctors', 'license_number')->where(fn ($q) => $q->where('clinic_id', $clinicId))],
            'room_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $doctor = Doctor::create($this->doctorPayloadFromRequest($request));

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
        $doctor = Doctor::findOrFail($id);
        $linkedStaff = Staff::query()
            ->where('role_type', 'doctor')
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        $pageTitle = __('doctors.page_title_edit');

        if ($request->ajax()) {
            return view('doctors.partials.edit', compact('doctor', 'linkedStaff', 'pageTitle'));
        }

        return view('doctors.edit', compact('doctor', 'linkedStaff', 'pageTitle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $doctor = Doctor::findOrFail($id);

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
