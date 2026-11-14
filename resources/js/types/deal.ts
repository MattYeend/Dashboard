export interface Deal {
    id: number;
    organisation_id: number;
    title: string;
    description: string | null;
    pipeline_id: number | null;
    stage_id: number | null;
    status_id: number | null;
    company_id: number | null;
    invoice_id: number | null;
    value: number;
    currency: string;
    formatted_value: string;
    probability: number;
    expected_close_date: string | null;
    closed_at: string | null;
    meta: Record<string, unknown> | null;
    tags?: { id: number; name: string }[];
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    pipeline?: { id: number; title: string } | null;
    stage?: {
        id: number;
        title: string;
        background_colour: string | null;
        text_colour: string | null;
    } | null;
    status?: {
        id: number;
        title: string;
        background_colour: string | null;
        text_colour: string | null;
    } | null;
    company?: { id: number; name: string } | null;
    invoice?: { id: number; invoice_number: string } | null;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}