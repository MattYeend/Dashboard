export interface PaymentFormData {
    invoice_id: string;
    method: string;
    amount: string;
    paid_at: string;
    reference: string | null;
    notes: string | null;
}

export interface RefundFormData {
    amount: string;
    method: string;
    paid_at: string;
    reference: string | null;
    notes: string;
}