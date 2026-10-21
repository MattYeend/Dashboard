/**
 * Convert a form input value to a number, or null when the field is empty or not numeric.
 */
export function parseNullableNumber(
    value: string | number | null | undefined,
): number | null {
    if (value === '' || value === null || value === undefined) {
        return null;
    }

    const parsed = Number(value);

    return Number.isNaN(parsed) ? null : parsed;
}
