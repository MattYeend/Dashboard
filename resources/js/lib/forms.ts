import type {
    CampaignSegmentCriterion,
    CampaignSegmentCriterionValue,
} from '@/types';
import type {
    CampaignFormData,
    CampaignSegmentCriterionFormData,
    CampaignSegmentFormData,
} from '@/types/forms';

export function nullIfBlank(value: string | null | undefined): string | null {
    if (value === null || value === undefined) {
        return null;
    }

    return value.trim() === '' ? null : value;
}

export function numberOrNull(value: string | number | null): number | null {
    if (value === null || value === '') {
        return null;
    }

    return Number(value);
}

export function criterionToFormData(
    criterion: CampaignSegmentCriterion,
): CampaignSegmentCriterionFormData {
    return {
        field: criterion.field,
        operator: criterion.operator,
        value: String(criterion.value),
    };
}

export function parseCriterionValue(
    value: string,
): CampaignSegmentCriterionValue {
    const trimmed = value.trim();

    if (trimmed === 'true') {
        return true;
    }

    if (trimmed === 'false') {
        return false;
    }

    if (trimmed !== '' && !Number.isNaN(Number(trimmed))) {
        return Number(trimmed);
    }

    return trimmed;
}

export function prepareCampaignSegmentPayload(data: CampaignSegmentFormData): {
    name: string;
    description: string | null;
    criteria: CampaignSegmentCriterion[] | null;
} {
    return {
        name: data.name,
        description: nullIfBlank(data.description),
        criteria:
            data.criteria.length > 0
                ? data.criteria.map((criterion) => ({
                      field: criterion.field,
                      operator: criterion.operator,
                      value: parseCriterionValue(criterion.value),
                  }))
                : null,
    };
}

export function prepareCampaignPayload(data: CampaignFormData) {
    return {
        ...data,
        email_template_id: numberOrNull(data.email_template_id),
        campaign_segment_id: numberOrNull(data.campaign_segment_id),
        owner_id: numberOrNull(data.owner_id),
        subject: nullIfBlank(data.subject),
        from_name: nullIfBlank(data.from_name),
        reply_to: nullIfBlank(data.reply_to),
        scheduled_at: nullIfBlank(data.scheduled_at),
    };
}
