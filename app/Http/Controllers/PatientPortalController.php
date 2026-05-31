<?php

namespace App\Http\Controllers;

use App\Services\Emr\PatientPortalService;
use App\Services\Emr\PatientQrService;
use Illuminate\View\View;

final class PatientPortalController extends Controller
{
    public function __construct(
        private readonly PatientQrService $qr,
        private readonly PatientPortalService $portal,
    ) {}

    public function show(string $token): View
    {
        $patient = $this->qr->resolvePatient($token);
        abort_unless($patient, 404);

        return view('portal.patient', [
            'patient' => $patient,
            'snapshot' => $this->portal->snapshot($patient),
        ]);
    }
}
