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
