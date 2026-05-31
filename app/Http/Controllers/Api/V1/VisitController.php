<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\VisitResource;
use App\Models\Visit;
use App\Services\Api\VisitApiService;
use App\Support\Queries\VisitListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class VisitController extends ApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Visit::class);

        $query = VisitListQuery::fromRequest($request);
        $user = $request->user();

        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if ($doc) {
                $query->where('doctor_id', $doc->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (! $request->filled('date')) {
            $query->whereDate('visit_date', now()->toDateString());
        }

        $paginator = $query
            ->orderByRaw("CASE status WHEN '".Visit::STATUS_WAITING."' THEN 0 WHEN '".Visit::STATUS_IN_PROGRESS."' THEN 1 ELSE 2 END")
            ->orderBy('id')
            ->paginate(min(50, max(1, (int) $request->input('per_page', 20))));

        return $this->ok(
            VisitResource::collection($paginator->items()),
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }

    public function show(Visit $visit): JsonResponse
    {
        $this->authorize('view', $visit);
        $visit->load(['patient:id,full_name,file_number', 'doctor:id,full_name', 'appointment:id,appointment_date']);

        return $this->ok(new VisitResource($visit));
    }

    public function updateStatus(Request $request, Visit $visit, VisitApiService $visits): JsonResponse
    {
        $this->authorize('update', $visit);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', VisitApiService::allowedStatuses())],
        ]);

        $visit = $visits->updateStatus($visit, $validated['status']);
        $visit->load(['patient:id,full_name,file_number', 'doctor:id,full_name']);

        return $this->ok(new VisitResource($visit));
    }

    public function update(Request $request, Visit $visit, VisitApiService $visits): JsonResponse
    {
        $this->authorize('update', $visit);

        $validated = $request->validate(
            VisitApiService::clinicalValidationRules(),
            [],
            $this->validationAttributes(),
        );

        $visit = $visits->updateClinical($visit, $validated);
        $visit->load(['patient:id,full_name,file_number', 'doctor:id,full_name', 'appointment:id,appointment_date']);

        return $this->ok(new VisitResource($visit));
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'chief_complaint' => __('visits.attr_chief_complaint'),
            'diagnosis' => __('visits.attr_diagnosis'),
            'treatment_plan' => __('visits.attr_treatment_plan'),
            'notes' => __('visits.attr_notes'),
        ];
    }
}
