import { nullIfBlank, numberOrNull } from '@/lib/forms';
import type { CampaignFormData } from '@/types/forms';

export function formatDateTime(
    value: string | null,
    fallback = '-',
): string {
    if (!value) {
        return fallback;
    }

    const date = new Date(value);

    return Number.isNaN(date.getTime()) ? fallback : date.toLocaleString();
}

/**
 * Converts an ISO timestamp into the "YYYY-MM-DDTHH:mm" value that
 * <input type="datetime-local"> requires.
 */
export function toDateTimeLocal(value: string | null): string | null {
    if (!value) {
        return null;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    const pad = (part: number): string => String(part).padStart(2, '0');

    return (
        `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}` +
        `T${pad(date.getHours())}:${pad(date.getMinutes())}`
    );
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