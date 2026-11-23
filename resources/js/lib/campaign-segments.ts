import { nullIfBlank } from '@/lib/forms';
import type {
    CampaignSegmentCriterion,
    CampaignSegmentCriterionValue,
} from '@/types';
import type {
    CampaignSegmentCriterionFormData,
    CampaignSegmentFormData,
} from '@/types/forms';

export function humaniseKey(key: string): string {
    const text = key.replace(/_/g, ' ');

    return text.charAt(0).toUpperCase() + text.slice(1);
}

export function formatCriterionValue(
    value: CampaignSegmentCriterionValue,
): string {
    if (typeof value === 'boolean') {
        return value ? 'Yes' : 'No';
    }

    return String(value);
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
