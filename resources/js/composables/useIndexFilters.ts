import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref } from 'vue';

export interface IndexFilterOption {
    value: string;
    label: string;
}

export interface IndexFilterField {
    key: string;
    type: 'text' | 'select';
    placeholder?: string;
    options?: IndexFilterOption[];
}

export interface ExtraFilter {
    key: string;
    type: 'text' | 'select';
    placeholder?: string;
    options?: () => IndexFilterOption[];
}

interface Options {
    url: () => string;
    searchPlaceholder: string;
    sortFields: () => Record<string, string>;
    trashFilters?: () => Record<string, string>;
    extraFilters?: ExtraFilter[];
    defaultSortBy?: string;
    defaultSortDirection?: 'asc' | 'desc';
}

export function useIndexFilters(options: Options) {
    const params = new URLSearchParams(window.location.search);
    const extras = options.extraFilters ?? [];

    const filters = ref<Record<string, string>>({
        search: params.get('search') ?? '',
        ...Object.fromEntries(
            extras.map((filter) => [filter.key, params.get(filter.key) ?? '']),
        ),
        ...(options.trashFilters
            ? { trashed: params.get('trashed') ?? '' }
            : {}),
        sort_by: params.get('sort_by') ?? options.defaultSortBy ?? 'created_at',
        sort_direction:
            params.get('sort_direction') ??
            options.defaultSortDirection ??
            'asc',
    });

    const filterFields = computed<IndexFilterField[]>(() => {
        const fields: IndexFilterField[] = [
            {
                key: 'search',
                type: 'text',
                placeholder: options.searchPlaceholder,
            },
            ...extras.map((filter) => ({
                key: filter.key,
                type: filter.type,
                placeholder: filter.placeholder,
                options: filter.options?.(),
            })),
        ];

        if (options.trashFilters) {
            fields.push({
                key: 'trashed',
                type: 'select',
                options: Object.entries(options.trashFilters()).map(
                    ([value, label]) => ({ value, label }),
                ),
            });
        }

        fields.push(
            {
                key: 'sort_by',
                type: 'select',
                options: Object.entries(options.sortFields()).map(
                    ([value, label]) => ({ value, label: `Sort by ${label}` }),
                ),
            },
            {
                key: 'sort_direction',
                type: 'select',
                options: [
                    { value: 'asc', label: 'Ascending' },
                    { value: 'desc', label: 'Descending' },
                ],
            },
        );

        return fields;
    });

    const applyFilters = useDebounceFn((): void => {
        router.get(options.url(), filters.value, {
            preserveState: true,
            replace: true,
        });
    }, 300);

    return { filters, filterFields, applyFilters };
}
