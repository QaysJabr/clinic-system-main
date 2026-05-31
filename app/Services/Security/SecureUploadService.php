<?php

namespace App\Services\Security;

use App\Contracts\MalwareScanner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SecureUploadService
{
    /**
     * @return array{disk: string, path: string, mime: string, size: int, original_name: string}
     */
    public function storeAttachment(UploadedFile $file, string $directory): array
    {
        $this->validateFile($file);

        $disk = (string) config('security.uploads.attachments_disk', 'private');
        if (! array_key_exists($disk, config('filesystems.disks', []))) {
            $disk = 'local';
        }

        $extension = $this->resolveExtension($file);
        $storedName = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs($directory, $storedName, $disk);

        return [
            'disk' => $disk,
            'path' => $path,
            'mime' => (string) ($file->getMimeType() ?? 'application/octet-stream'),
            'size' => (int) $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    public function validateFile(UploadedFile $file): void
    {
        $maxKb = (int) config('security.uploads.max_kb', 10240);
        $allowedMimes = config('security.uploads.allowed_mimes', []);
        $allowedExtensions = config('security.uploads.allowed_extensions', []);

        if ($file->getSize() > $maxKb * 1024) {
            throw ValidationException::withMessages([
                'file' => [__('security.upload_too_large', ['max' => $maxKb])],
            ]);
        }

        $detectedMime = $file->getMimeType();
        if ($detectedMime && ! in_array($detectedMime, $this->mimeMap($allowedMimes), true)) {
            throw ValidationException::withMessages([
                'file' => [__('security.upload_invalid_type')],
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'file' => [__('security.upload_invalid_type')],
            ]);
        }

        $scannerClass = config('security.uploads.malware_scanner');
        if (is_string($scannerClass) && class_exists($scannerClass)) {
            $scanner = app($scannerClass);
            if ($scanner instanceof MalwareScanner) {
                $scanner->assertClean($file);
            }
        }
    }

    public function diskFor(?string $storageDisk): string
    {
        $disk = $storageDisk ?: 'public';

        return array_key_exists($disk, config('filesystems.disks', [])) ? $disk : 'public';
    }

    public function deleteIfExists(?string $disk, ?string $path): void
    {
        if (! $path) {
            return;
        }

        $resolved = $this->diskFor($disk);
        if (Storage::disk($resolved)->exists($path)) {
            Storage::disk($resolved)->delete($path);
        }
    }

    /**
     * @param  array<int, string>  $extensions
     * @return array<int, string>
     */
    private function mimeMap(array $extensions): array
    {
        $map = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        return array_values(array_intersect_key($map, array_flip($extensions)));
    }

    private function resolveExtension(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $allowed = config('security.uploads.allowed_extensions', ['pdf', 'jpg', 'jpeg', 'png']);

        if (in_array($ext, $allowed, true)) {
            return $ext;
        }

        return match ($file->getMimeType()) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            default => 'bin',
        };
    }
}
