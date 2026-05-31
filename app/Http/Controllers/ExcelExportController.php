<?php

namespace App\Http\Controllers;

use App\Services\Exports\ExcelExportSheets;
use App\Services\Exports\ExportDispatcher;
use App\Support\AuditLogger;
use App\Support\Excel\XlsxExportResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportController extends Controller
{
    public function __construct(
        private readonly ExcelExportSheets $sheets,
    ) {}

    public function patients(Request $request): StreamedResponse|RedirectResponse
    {
        AuditLogger::log(
            'export',
            'patients',
            null,
            __('patients.audit_export_patients_excel'),
            null,
            ['filters' => $request->query()]
        );

        $filename = 'patients-'.now()->format('Y-m-d_His').'.xlsx';

        return ExportDispatcher::excel($request, 'patients', $filename, fn (): StreamedResponse => XlsxExportResponse::stream(
            $filename,
            fn (Writer $writer) => $this->sheets->patients($writer, $request),
        ));
    }

    public function invoices(Request $request): StreamedResponse|RedirectResponse
    {
        AuditLogger::log(
            'export',
            'invoices',
            null,
            __('invoices.audit_export_excel'),
            null,
            ['filters' => $request->query()]
        );

        $filename = 'invoices-'.now()->format('Y-m-d_His').'.xlsx';

        return ExportDispatcher::excel($request, 'invoices', $filename, fn (): StreamedResponse => XlsxExportResponse::stream(
            $filename,
            fn (Writer $writer) => $this->sheets->invoices($writer, $request),
        ));
    }

    public function appointments(Request $request): StreamedResponse|RedirectResponse
    {
        AuditLogger::log(
            'export',
            'appointments',
            null,
            __('appointments.audit_export_excel'),
            null,
            ['filters' => $request->query()]
        );

        $filename = 'appointments-'.now()->format('Y-m-d_His').'.xlsx';

        return ExportDispatcher::excel($request, 'appointments', $filename, fn (): StreamedResponse => XlsxExportResponse::stream(
            $filename,
            fn (Writer $writer) => $this->sheets->appointments($writer, $request),
        ));
    }

    public function visits(Request $request): StreamedResponse|RedirectResponse
    {
        AuditLogger::log(
            'export',
            'visits',
            null,
            __('visits.audit_export_excel'),
            null,
            ['filters' => $request->query()]
        );

        $filename = 'visits-'.now()->format('Y-m-d_His').'.xlsx';

        return ExportDispatcher::excel($request, 'visits', $filename, fn (): StreamedResponse => XlsxExportResponse::stream(
            $filename,
            fn (Writer $writer) => $this->sheets->visits($writer, $request),
        ));
    }
}
