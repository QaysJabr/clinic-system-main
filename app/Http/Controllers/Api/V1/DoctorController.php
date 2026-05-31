<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\DoctorResource;
use App\Models\Doctor;
use App\Support\ClinicPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DoctorController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->can(ClinicPermissions::MANAGE_APPOINTMENTS) === true,
            403,
        );

        $query = Doctor::query()
            ->where('status', 'active')
            ->orderBy('full_name');

        $user = $request->user();
        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if ($doc) {
                $query->where('id', $doc->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->input('q')).'%';
            $query->where('full_name', 'like', $term);
        }

        $doctors = $query->limit(100)->get(['id', 'full_name', 'status']);

        return $this->ok(DoctorResource::collection($doctors));
    }
}
