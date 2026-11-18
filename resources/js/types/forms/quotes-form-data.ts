export interface QuoteFormData {
    quotable_type: string;
    quotable_id: number | null;
    title: string;
    description: string | null;
    notes: string | null;
    terms: string | null;
    discount_amount: string;
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

export interface QuoteItemAbilities {
    create: boolean;
    import: boolean;
    export: boolean;
}

export interface ProductOption {
    id: number;
    name: string;
}

export interface SelectOption {
    value: number | string;
    label: string;
}
