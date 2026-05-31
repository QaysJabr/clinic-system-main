<?php

namespace App\Http\Controllers;

use App\Services\Emr\PatientQrService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PatientLookupController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PatientQrService $qr,
    ) {}

    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $patient = $this->qr->resolvePatient($token);

        abort_unless($patient, 404);

        $this->authorize('view', $patient);

        return redirect()->route('patients.show', $patient);
    }
}
