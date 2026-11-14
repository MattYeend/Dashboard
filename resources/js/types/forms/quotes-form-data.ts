export interface QuoteFormData {
    quotable_type: string;
    quotable_id: number | null;
    title: string;
    description: string | null;
    notes: string | null;
    terms: string | null;
    subtotal: string;
    discount_amount: string;
    tax_amount: string;
    currency: string;
    issued_at: string | null;
    valid_until: string | null;
}

export interface QuoteAbilities {
    update: boolean;
    delete: boolean;
    send: boolean;
    accept: boolean;
    decline: boolean;
    convert: boolean;
}

export interface SelectOption {
    value: number | string;
    label: string;
}
