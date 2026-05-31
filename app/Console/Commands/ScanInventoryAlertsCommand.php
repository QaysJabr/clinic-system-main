<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Services\Inventory\InventoryAlertService;
use Illuminate\Console\Command;

final class ScanInventoryAlertsCommand extends Command
{
    protected $signature = 'inventory:scan-alerts {--clinic= : Limit to one clinic id}';

    protected $description = 'Scan clinics for low-stock and expiring inventory items and send notifications';

    public function handle(InventoryAlertService $alerts): int
    {
        $clinicId = $this->option('clinic');

        $query = Clinic::query()->select('id');
        if ($clinicId !== null && $clinicId !== '') {
            $query->where('id', (int) $clinicId);
        }

        $count = 0;
        $query->orderBy('id')->each(function (Clinic $clinic) use ($alerts, &$count): void {
            $alerts->scanClinic((int) $clinic->id);
            $count++;
        });

        $this->info("Scanned {$count} clinic(s) for inventory alerts.");

        return self::SUCCESS;
    }
}
