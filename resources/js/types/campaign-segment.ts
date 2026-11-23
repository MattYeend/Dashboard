export type CampaignSegmentCriterionValue = string | number | boolean;
 
export interface CampaignSegmentCriterion {
    field: string;
    operator: string;
    value: CampaignSegmentCriterionValue;
}
 
export interface CampaignSegment {
    id: number;
    name: string;
    description: string | null;
    criteria: CampaignSegmentCriterion[] | null;
    estimated_recipients_count: number | null;
    last_refreshed_at: string | null;
    meta: Record<string, unknown> | null;
    created_by: number | null;
    updated_by: number | null;
    deleted_by: number | null;
    restored_by: number | null;
    restored_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    creator?: { name: string } | null;
    updater?: { name: string } | null;
    deleter?: { name: string } | null;
    restorer?: { name: string } | null;
}