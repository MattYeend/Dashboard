/**
 * Format a decimal string from the server as a currency amount.
 */
export function formatMoney(value: string, currency = 'GBP'): string {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency,
    }).format(Number(value));
}

/**
 * Format an ISO date string as a UK date.
 */
export function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}
