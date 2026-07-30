<?php

namespace App\Services\InvoiceItems;

use App\Http\Requests\InvoiceItems\StoreInvoiceItemRequest;
use App\Http\Requests\InvoiceItems\UpdateInvoiceItemRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer
    ) {}

    /**
     * Create a new invoice item.
     */
    public function store(
        StoreInvoiceItemRequest $request,
        Invoice $invoice
    ): InvoiceItem {
        return $this->creator->create(
            $request->validated(),
            $invoice->id,
            $request->user()->id
        );
    }

    /**
     * Update an existing invoice item.
     */
    public function update(
        UpdateInvoiceItemRequest $request,
        InvoiceItem $invoiceItem
    ): InvoiceItem {
        return $this->updater->update(
            $invoiceItem,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete an invoice item.
     */
    public function destroy(
        InvoiceItem $invoiceItem,
        User $actor
    ): void {
        $this->destructor->delete(
            $invoiceItem,
            $actor->id
        );
    }

    /**
     * Restore a soft-deleted invoice item, scoped to its parent invoice.
     */
    public function restore(
        Invoice $invoice,
        int $id,
        User $actor
    ): InvoiceItem {
        $invoiceItem = $invoice->items()->withTrashed()->findOrFail($id);

        return $this->restorer->restore(
            $invoiceItem,
            $actor->id
        );
    }

    /**
     * Force delete an invoice item, scoped to its parent invoice,
     * permanently removing it from the database.
     */
    public function forceDelete(
        Invoice $invoice,
        int $id,
        User $actor
    ): void {
        $invoiceItem = $invoice->items()->withTrashed()->findOrFail($id);
        $this->destructor->forceDelete(
            $invoiceItem,
            $actor->id
        );
    }

    /**
     * Bulk restore invoice items, scoped to their parent invoice.
     */
    public function bulkRestore(
        Invoice $invoice,
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $invoiceItems = $invoice->items()
            ->onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($invoiceItems as $invoiceItem) {
            /** @var InvoiceItem $invoiceItem */
            $authoriseCallback(
                $invoiceItem
            );
            $this->restorer->restore(
                $invoiceItem,
                $actor->id
            );
            $restored[] = $invoiceItem->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($invoiceItems->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete invoice items, scoped to their parent invoice.
     */
    public function bulkDelete(
        Invoice $invoice,
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $invoiceItems = $invoice->items()
            ->whereIn('id', $requestedIds)
            ->get();

        $deleted = [];

        foreach ($invoiceItems as $invoiceItem) {
            /** @var InvoiceItem $invoiceItem */
            $authoriseCallback(
                $invoiceItem
            );

            $this->destructor->delete(
                $invoiceItem,
                $actor->id
            );
            $deleted[] = $invoiceItem->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($invoiceItems->pluck('id'))
                ->values()
                ->all(),
        ];
    }
}
