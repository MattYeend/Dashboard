export function formatDecimalMoney(
    value: string | number | null | undefined,
    currency: string,
): string {
    const amount = Number.parseFloat(String(value ?? ''));

    if (!Number.isFinite(amount)) {
        return '-';
    }

    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency,
    }).format(amount);
}
