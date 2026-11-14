export interface DealFormData {
    title: string;
    description: string | null;
    tag_ids: number[];
    pipeline_id: number | null;
    stage_id: number | null;
    status_id: number | null;
    company_id: number | null;
    invoice_id: number | null;
    value: number;
    currency: string;
    probability: number;
    expected_close_date: string | null;
    closed_at: string | null;
}