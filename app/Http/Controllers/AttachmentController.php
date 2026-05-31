<?php

namespace App\Http\Controllers;

use App\Enums\AttachmentCategory;
use App\Models\Attachment;
use App\Models\Patient;
use App\Models\Visit;
use App\Services\Attachments\AttachmentThumbnailService;
use App\Services\Security\SecureUploadService;
use App\Support\AuditLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AttachmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly SecureUploadService $secureUpload,
        private readonly AttachmentThumbnailService $thumbnails,
    ) {}

    public function storeForPatient(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('update', $patient);
        abort_unless($request->user()->can('manage attachments'), 403);

        $validated = $this->validatedUpload($request);

        $attachment = $this->storeFile(
            $validated,
            $request,
            'attachments/patients',
            $patient->id,
            null
        );

        AuditLogger::log(
            'create',
            'attachments',
            $attachment->id,
            __('emr.audit_attachment_upload', ['name' => $attachment->file_name]),
            null,
            $this->attachmentSnapshot($attachment)
        );

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', __('emr.flash_attachment_uploaded'));
    }

    public function storeForVisit(Request $request, Visit $visit): RedirectResponse
    {
        $this->authorize('update', $visit);
        abort_unless($request->user()->can('manage attachments'), 403);

        $validated = $this->validatedUpload($request);

        $attachment = $this->storeFile(
            $validated,
            $request,
            'attachments/visits',
            $visit->patient_id,
            $visit->id
        );

        AuditLogger::log(
            'create',
            'attachments',
            $attachment->id,
            __('emr.audit_attachment_upload', ['name' => $attachment->file_name]),
            null,
            $this->attachmentSnapshot($attachment)
        );

        return redirect()
            ->route('visits.show', $visit)
            ->with('success', __('emr.flash_attachment_uploaded'));
    }

    public function download(Attachment $attachment)
    {
        $this->authorize('view', $attachment);

        $disk = $attachment->storageDisk();
        if (! Storage::disk($disk)->exists($attachment->file_path)) {
            abort(404);
        }

        AuditLogger::security(
            'download',
            'attachments',
            $attachment->id,
            __('emr.audit_attachment_download', ['name' => $attachment->file_name]),
            null,
            $this->attachmentSnapshot($attachment)
        );

        return Storage::disk($disk)->download($attachment->file_path, $attachment->file_name);
    }

    public function preview(Attachment $attachment): Response
    {
        $this->authorize('view', $attachment);

        $disk = $attachment->storageDisk();
        $thumb = $this->thumbnails->thumbnailPath($attachment);
        $path = $thumb ?? $attachment->file_path;
        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path, $attachment->file_name, [
            'Content-Disposition' => 'inline',
        ]);
    }

    public function destroy(Request $request, Attachment $attachment): RedirectResponse
    {
        $this->authorize('manage', $attachment);

        $patientId = $attachment->patient_id;
        $visitId = $attachment->visit_id;
        $attachmentId = $attachment->id;
        $old = $this->attachmentSnapshot($attachment);

        $attachment->deleteFileFromStorage();
        $attachment->delete();

        AuditLogger::log(
            'delete',
            'attachments',
            $attachmentId,
            __('emr.audit_attachment_delete', ['name' => $old['file_name'] ?? '']),
            $old,
            null
        );

        if ($visitId !== null) {
            return redirect()
                ->route('visits.show', $visitId)
                ->with('success', __('emr.flash_attachment_deleted'));
        }

        return redirect()
            ->route('patients.show', $patientId)
            ->with('success', __('emr.flash_attachment_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedUpload(Request $request): array
    {
        return $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'in:'.implode(',', AttachmentCategory::values())],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function storeFile(array $validated, Request $request, string $directory, int $patientId, ?int $visitId): Attachment
    {
        $stored = $this->secureUpload->storeAttachment($validated['file'], $directory);

        $attachment = Attachment::query()->create([
            'patient_id' => $patientId,
            'visit_id' => $visitId,
            'file_name' => $stored['original_name'],
            'file_path' => $stored['path'],
            'storage_disk' => $stored['disk'],
            'file_type' => $stored['mime'],
            'category' => $validated['category'] ?? AttachmentCategory::Document->value,
            'file_size' => $stored['size'],
            'notes' => $validated['notes'] ?? null,
            'uploaded_by' => $request->user()->id,
        ]);

        $this->thumbnails->maybeGenerate($attachment);

        return $attachment;
    }

    /**
     * @return array<string, mixed>
     */
    private function attachmentSnapshot(Attachment $attachment): array
    {
        return [
            'file_name' => $attachment->file_name,
            'patient_id' => $attachment->patient_id,
            'visit_id' => $attachment->visit_id,
            'category' => $attachment->category,
            'file_size' => $attachment->file_size,
        ];
    }
}
