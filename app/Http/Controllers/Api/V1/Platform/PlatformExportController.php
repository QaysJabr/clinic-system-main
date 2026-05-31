<?php

namespace App\Http\Controllers\Api\V1\Platform;

use App\Http\Controllers\Api\ApiController;
use App\Models\Clinic;
use App\Services\Platform\PlatformCsvExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PlatformExportController extends ApiController
{
    public function __construct(
        private readonly PlatformCsvExportService $exports,
    ) {}

    public function clinics(Request $request): StreamedResponse
    {
        return $this->exports->clinics($request);
    }

    public function payments(Request $request): StreamedResponse
    {
        return $this->exports->payments($request);
    }

    public function clinicPayments(Request $request, Clinic $clinic): StreamedResponse
    {
        return $this->exports->clinicPayments($request, $clinic);
    }
}
