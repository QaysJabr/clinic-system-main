<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\PatientResource;
use App\Models\Patient;
use App\Services\Api\PatientApiService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PatientController extends ApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Patient::class);

        $query = Patient::query()->orderBy('full_name');

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('file_number', 'like', $term);
            });
        }

        $paginator = $query->paginate(min(50, max(1, (int) $request->input('per_page', 20))));

        return $this->ok(
            PatientResource::collection($paginator->items()),
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }

    public function store(Request $request, PatientApiService $patients): JsonResponse
    {
        $this->authorize('create', Patient::class);

        $validated = $request->validate(
            PatientApiService::validationRules(),
            [],
            $this->validationAttributes(),
        );

        $patient = $patients->create($validated);

        return $this->created(new PatientResource($patient));
    }

    public function show(Patient $patient): JsonResponse
    {
        $this->authorize('view', $patient);

        return $this->ok(new PatientResource($patient));
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'file_number' => __('patients.field_file_number'),
            'full_name' => __('patients.field_full_name'),
            'phone' => __('patients.field_phone'),
            'date_of_birth' => __('patients.field_date_of_birth'),
            'gender' => __('patients.field_gender'),
            'notes' => __('patients.field_notes'),
        ];
    }
}
