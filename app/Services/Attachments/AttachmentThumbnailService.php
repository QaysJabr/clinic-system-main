<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Generates optional JPEG thumbnails for image attachments (GD when available).
 */
final class AttachmentThumbnailService
{
    public function maybeGenerate(Attachment $attachment): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        if (! str_contains((string) $attachment->file_type, 'image')) {
            return null;
        }

        $disk = $attachment->storageDisk();
        if (! Storage::disk($disk)->exists($attachment->file_path)) {
            return null;
        }

        $contents = Storage::disk($disk)->get($attachment->file_path);
        $source = @imagecreatefromstring($contents);
        if ($source === false) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $maxWidth = (int) config('performance.attachments.thumbnail_max_width', 480);

        if ($width <= $maxWidth) {
            imagedestroy($source);

            return null;
        }

        $ratio = $maxWidth / $width;
        $newHeight = (int) round($height * $ratio);
        $thumb = imagecreatetruecolor($maxWidth, $newHeight);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
        imagedestroy($source);

        ob_start();
        imagejpeg($thumb, null, (int) config('performance.attachments.thumbnail_quality', 82));
        imagedestroy($thumb);
        $binary = ob_get_clean();

        if ($binary === false || $binary === '') {
            return null;
        }

        $thumbPath = Str::beforeLast($attachment->file_path, '.').'_thumb.jpg';
        Storage::disk($disk)->put($thumbPath, $binary);

        return $thumbPath;
    }

    public function thumbnailPath(Attachment $attachment): ?string
    {
        $candidate = Str::beforeLast($attachment->file_path, '.').'_thumb.jpg';
        $disk = $attachment->storageDisk();

        return Storage::disk($disk)->exists($candidate) ? $candidate : null;
    }
}
