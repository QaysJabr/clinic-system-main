<?php

namespace App\Support;

use App\Models\ClinicSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

/**
 * Logo src for print/PDF documents (filesystem path, data URI, or public URL).
 */
final class ClinicDocumentLogo
{
    public static function src(ClinicSetting $clinic, bool $exportRender = false): ?string
    {
        $relative = $clinic->normalizedLogoRelativePath();
        if ($relative === null || ! Storage::disk('public')->exists($relative)) {
            return null;
        }

        if (View::shared('clinicPdfExport')) {
            return str_replace('\\', '/', Storage::disk('public')->path($relative));
        }

        if ($exportRender) {
            return self::dataUri(Storage::disk('public')->path($relative));
        }

        return $clinic->logoPublicUrl();
    }

    private static function dataUri(string $absolutePath): ?string
    {
        if (! is_file($absolutePath)) {
            return null;
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($absolutePath));
    }
}
