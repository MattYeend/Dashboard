import type { PipelineStage } from './pipeline-stage';

export interface Pipeline {
    id: number;
    organisation_id: number;
    title: string;
    description: string | null;
    is_default: boolean;
    status_id: number | null;
    assigned_to: number | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    status?: {
        id: number;
        title: string;
        background_colour: string | null;
        text_colour: string | null;
    } | null;
    assignee?: { id: number; name: string } | null;
    stages?: PipelineStage[];
    stages_count?: number;
    creator?: { name: string };
    updater?: { name: string };
    deleter?: { name: string };
    restorer?: { name: string };
}
