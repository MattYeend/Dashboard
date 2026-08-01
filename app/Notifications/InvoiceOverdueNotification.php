<?php

namespace App\Notifications;

use App\Models\Invoice;

class InvoiceOverdueNotification extends BaseNotification
{
    /**
     * @param Invoice $invoice The invoice that is overdue.
     */
    public function __construct(protected Invoice $invoice) {}

    /**
     * Machine-readable category used for filtering/icons in the UI.
     */
    protected function type(): string
    {
        return 'invoice_overdue';
    }

    /**
     * Heading shown in the notifications list.
     */
    protected function title(): string
    {
        return 'Invoice overdue';
    }

    /**
     * Body text shown in the notifications list.
     */
    protected function body(): string
    {
        return "Invoice {$this->invoice->invoice_number} is overdue";
    }

    /**
     * URL the user is taken to when they click the notification.
     */
    protected function actionUrl(): ?string
    {
        return route('invoices.show', $this->invoice->id);
    }

    /**
     * Related model class, used to link the notification back to its subject.
     */
    protected function subjectType(): ?string
    {
        return Invoice::class;
    }

    /**
     * Primary key of the related model.
     */
    protected function subjectId(): ?int
    {
        return $this->invoice->id;
    }
}