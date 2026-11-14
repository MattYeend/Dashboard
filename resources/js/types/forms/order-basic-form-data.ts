export interface OrderBasicFormData {
    title: string;
    description: string | null;
    notes: string | null;
    subtotal: number;
    discount_amount: number;
    tax_amount: number;
    total_amount: number;
    currency: string;
    ordered_at: string | null;
    due_at: string | null;
    completed_at: string | null;
    status_id: number | null;
}