import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref } from 'vue';

export interface IndexFilterField {
    key: string;
    type: 'text' | 'select';
    placeholder?: string;
    options?: Array<{ value: string; label: string }>;
}

interface Options {
    url: () => string;
    searchPlaceholder: string;
    sortFields: () => Record<string, string>;
    trashFilters: () => Record<string, string>;
    defaultSortBy?: string;
    defaultSortDirection?: 'asc' | 'desc';
}

export function useIndexFilters(options: Options) {
    const params = new URLSearchParams(window.location.search);

    const filters = ref({
        search: params.get('search') ?? '',
        trashed: params.get('trashed') ?? '',
        sort_by: params.get('sort_by') ?? options.defaultSortBy ?? 'created_at',
        sort_direction:
            params.get('sort_direction') ??
            options.defaultSortDirection ??
            'asc',
    });

    const filterFields = computed<IndexFilterField[]>(() => [
        {
            key: 'search',
            type: 'text',
            placeholder: options.searchPlaceholder,
        },
        {
            key: 'trashed',
            type: 'select',
            options: Object.entries(options.trashFilters()).map(
                ([value, label]) => ({ value, label }),
            ),
        },
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
    ]);

    const applyFilters = useDebounceFn((): void => {
        router.get(options.url(), filters.value, {
            preserveState: true,
            replace: true,
        });
    }, 300);

    return { filters, filterFields, applyFilters };
}
