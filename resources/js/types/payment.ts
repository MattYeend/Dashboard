export interface Payment {
    id: number;
    invoice_id: number;
    status_id: number;
    parent_payment_id: number | null;
    type: 'payment' | 'refund';
    method: string;
    method_label: string;
    amount: string;
    currency: string;
    reference: string | null;
    stripe_payment_intent_id: string | null;
    paid_at: string;
    notes: string | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    refundable_amount: string | null;
    can_refund: boolean;
    is_locked: boolean;
    status?: {
        id: number;
        title: string;
        system_key: string | null;
        background_colour: string;
        text_colour: string;
    } | null;
    invoice?: { id: number; invoice_number: string; amount: string } | null;
    creator?: { name: string } | null;
    updater?: { name: string } | null;
    deleter?: { name: string } | null;
    restorer?: { name: string } | null;
}

export interface PaymentMethodOption {
    value: string;
    label: string;
}

export interface PayableInvoiceOption {
    id: number;
    invoice_number: string;
    amount: string;
    amount_paid: string;
    balance_due: string;
}
