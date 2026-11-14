export interface PipelineStage {
    id: number;
    organisation_id: number;
    pipeline_id: number;
    title: string;
    description: string | null;
    position: number;
    background_colour: string;
    text_colour: string;
    is_won: boolean;
    is_lost: boolean;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    pipeline?: { id: number; title: string } | null;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}
