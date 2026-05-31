<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\InvoiceResource;
use App\Models\Invoice;
use App\Support\Queries\InvoiceListQuery;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class InvoiceController extends ApiController
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Invoice::class);

        $query = InvoiceListQuery::fromRequest($request);
        $user = $request->user();

        if ($user && $user->hasRole('doctor') && ! $user->hasRole('admin')) {
            $doc = $user->linkedDoctor();
            if ($doc) {
                $query->where('doctor_id', $doc->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $paginator = $query
            ->orderByDesc('created_at')
            ->paginate(min(50, max(1, (int) $request->input('per_page', 20))));

        return $this->ok(
            InvoiceResource::collection($paginator->items()),
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        );
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);
        $invoice->load(['patient:id,full_name', 'treatingDoctor:id,full_name']);

        return $this->ok(new InvoiceResource($invoice));
    }
}
