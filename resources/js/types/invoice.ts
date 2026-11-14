import type { InvoiceItem } from './invoice-item';

export interface Invoice {
    id: number;
    organisation_id: number;
    invoice_number: string;
    company_id: number | null;
    order_id: number | null;
    status_id: number | null;
    issue_date: string | null;
    due_date: string | null;
    sent_at: string | null;
    paid_at: string | null;
    subtotal: number;
    tax_total: number;
    total: number;
    currency: string;
    formatted_total: string;
    notes: string | null;
    items?: InvoiceItem[];
    items_count?: number;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    company?: { id: number; name: string } | null;
    contact?: {
        id: number;
        name: string | null;
        phone: string | null;
        email: string | null;
        address: string | null;
        city: string | null;
        postal_code: string | null;
        country: string | null;
    } | null;
    order?: { id: number } | null;
    status?: {
        id: number;
        title: string;
        background_colour: string | null;
        text_colour: string | null;
    } | null;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}