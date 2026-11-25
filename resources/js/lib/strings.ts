import type { CampaignSegmentCriterionValue } from '@/types';

export function humaniseStatus(value: string | null): string {
    if (!value) {
        return '-';
    }

    const text = value.replace(/[_-]+/g, ' ').trim();

    return text.charAt(0).toUpperCase() + text.slice(1);
}

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
