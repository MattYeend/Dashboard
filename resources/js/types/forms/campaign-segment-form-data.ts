export interface CampaignSegmentCriterionFormData {
    field: string;
    operator: string;
    value: string;
}
 
export interface CampaignSegmentFormData {
    name: string;
    description: string | null;
    criteria: CampaignSegmentCriterionFormData[];
}