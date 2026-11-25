export function formatDateTime(value: string | null, fallback = '-'): string {
    if (!value) {
        return fallback;
    }

    const date = new Date(value);

    return Number.isNaN(date.getTime()) ? fallback : date.toLocaleString();
}

export function humaniseStatus(value: string | null): string {
    if (!value) {
        return '-';
    }

    const text = value.replace(/[_-]+/g, ' ').trim();

    return text.charAt(0).toUpperCase() + text.slice(1);
}
