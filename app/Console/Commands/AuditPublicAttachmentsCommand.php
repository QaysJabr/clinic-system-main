<?php

namespace App\Console\Commands;

use App\Models\Attachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AuditPublicAttachmentsCommand extends Command
{
    protected $signature = 'attachments:audit-public {--json : Output JSON summary}';

    protected $description = 'Audit legacy attachments still on the public disk (migration planning).';

    public function handle(): int
    {
        $legacy = Attachment::query()
            ->where(function ($q): void {
                $q->whereNull('storage_disk')
                    ->orWhere('storage_disk', 'public');
            })
            ->get(['id', 'file_path', 'storage_disk', 'patient_id', 'created_at']);

        $missing = 0;
        foreach ($legacy as $row) {
            if (! Storage::disk('public')->exists($row->file_path)) {
                $missing++;
            }
        }

        $summary = [
            'legacy_count' => $legacy->count(),
            'missing_on_public_disk' => $missing,
            'recommendation' => 'Migrate to private local disk via SecureUploadService; keep signed download routes.',
        ];

        if ($this->option('json')) {
            $this->line(json_encode($summary, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->info('Legacy public attachments: '.$summary['legacy_count']);
        $this->info('Missing files on public disk: '.$summary['missing_on_public_disk']);
        $this->line($summary['recommendation']);

        return self::SUCCESS;
    }
}
