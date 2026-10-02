export function formatDateTime(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export function truncate(value: string | null, length = 20): string {
    if (!value) {
        return '-';
    }

    return value.length > length ? `${value.slice(0, length)}…` : value;
}

export function formatDate(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

export function formatMoney(pence: number, currency = 'GBP'): string {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency,
    }).format(pence / 100);
}
