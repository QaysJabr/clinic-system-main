<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportDownloadController extends Controller
{
    public function __invoke(Request $request, string $path): StreamedResponse
    {
        abort_unless($request->user(), 403);

        $normalized = str_replace(['..', '\\'], ['', '/'], $path);
        $full = 'exports/'.$request->user()->id.'/'.$normalized;

        abort_unless(
            str_starts_with($full, 'exports/'.$request->user()->id.'/')
            && Storage::disk('local')->exists($full),
            404
        );

        return Storage::disk('local')->download($full);
    }
}
