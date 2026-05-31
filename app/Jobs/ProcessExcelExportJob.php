<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Exports\ExcelExportSheets;
use App\Services\InAppNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\Excel\XlsxExportResponse;
use OpenSpout\Writer\XLSX\Writer;

class ProcessExcelExportJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    /**
     * @param  array<string, mixed>  $query
     */
    public function __construct(
        public readonly string $type,
        public readonly int $userId,
        public readonly array $query,
        public readonly string $filename,
    ) {
        $this->onQueue(config('performance.queues.exports', 'default'));
    }

    public function handle(ExcelExportSheets $sheets, InAppNotificationService $notifications): void
    {
        $user = User::query()->findOrFail($this->userId);
        $request = Request::create('/', 'GET', $this->query);
        $request->setUserResolver(fn () => $user);

        $path = 'exports/'.$this->userId.'/'.now()->format('Ymd_His').'_'.$this->filename;
        $fullPath = Storage::disk('local')->path($path);
        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $writer = XlsxExportResponse::createWriter();
        $writer->openToFile($fullPath);

        match ($this->type) {
            'patients' => $sheets->patients($writer, $request),
            'invoices' => $sheets->invoices($writer, $request),
            'appointments' => $sheets->appointments($writer, $request),
            'visits' => $sheets->visits($writer, $request),
            default => throw new \InvalidArgumentException("Unknown export [{$this->type}]."),
        };

        $writer->close();

        $notifications->notifyExportReady($user, $this->filename, $path);
    }
}
