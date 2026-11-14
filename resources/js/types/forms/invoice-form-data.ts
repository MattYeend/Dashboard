export interface InvoiceContactFormData {
    phone: string | null;
    email: string | null;
    address: string | null;
    city: string | null;
    postal_code: string | null;
    country: string | null;
}

export interface InvoiceFormData {
    invoice_number: string;
    company_id: number | null;
    order_id: number | null;
    status_id: number | null;
    issue_date: string | null;
    due_date: string | null;
    subtotal: number | null;
    tax_total: number | null;
    total: number | null;
    currency: string;
    notes: string | null;
    contact: InvoiceContactFormData;
}
