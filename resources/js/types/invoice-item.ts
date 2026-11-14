export interface InvoiceItem {
    id: number;
    organisation_id: number;
    invoice_id: number;
    description: string;
    quantity: number;
    unit_price: number;
    tax_rate: string;
    total: number;
    position: number;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}
