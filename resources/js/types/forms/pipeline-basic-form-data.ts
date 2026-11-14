export interface PipelineBasicFormData {
    title: string;
    description: string | null;
    is_default: boolean;
    status_id: number | null;
    assigned_to: number | null;
}
