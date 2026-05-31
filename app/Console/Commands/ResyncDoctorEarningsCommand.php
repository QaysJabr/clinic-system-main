<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\DoctorEarningSyncService;
use Illuminate\Console\Command;

/**
 * إعادة مزامنة سجلات doctor_earnings لكل الفواتير المرتبطة بزيارة بعد تغيير منطق الاستحقاق.
 */
final class ResyncDoctorEarningsCommand extends Command
{
    protected $signature = 'doctor-earnings:resync {--chunk=200 : عدد الفواتير لكل دفعة}';

    protected $description = 'Resync doctor percentage earnings from all invoices linked to visits';

    public function handle(DoctorEarningSyncService $sync): int
    {
        $chunk = max(50, (int) $this->option('chunk'));
        $this->info('Resyncing doctor_earnings from invoices (visit_id not null)...');

        $count = 0;
        Invoice::query()
            ->whereNotNull('visit_id')
            ->orderBy('id')
            ->chunkById($chunk, function ($invoices) use ($sync, &$count): void {
                foreach ($invoices as $invoice) {
                    $sync->syncFromInvoice($invoice);
                    $count++;
                }
            });

        $this->info("Processed {$count} invoice(s).");

        return self::SUCCESS;
    }
}
