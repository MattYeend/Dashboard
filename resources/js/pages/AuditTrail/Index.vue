<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import auditTrail, { index } from '@/routes/audit-trail';
import type {
    AuditLogEntry,
    AuditTrailPermissionsMeta,
    Pagination as PaginationMeta,
    PaginationLink,
} from '@/types';
import AuditTrailTable from './components/AuditTrailTable.vue';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Audit trail', href: index().url }],
    },
});

interface Props {
    logs: {
        data: AuditLogEntry[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: AuditTrailPermissionsMeta;
    sort_fields: Record<string, string>;
    action_options: Record<string, string>;
}

const props = defineProps<Props>();

const urlParams = new URLSearchParams(window.location.search);

const filters = ref({
    search: urlParams.get('search') ?? '',
    action: urlParams.get('action') ?? '',
    date_from: urlParams.get('date_from') ?? '',
    date_to: urlParams.get('date_to') ?? '',
    sort_by: urlParams.get('sort_by') ?? 'created_at',
    sort_direction: urlParams.get('sort_direction') ?? 'desc',
});

const filterFields = [
    { key: 'search', type: 'text' as const, placeholder: 'Search audit trail…' },
    {
        key: 'action',
        type: 'select' as const,
        get options() {
            return [
                { value: '', label: 'All actions' },
                ...Object.entries(props.action_options).map(([value, label]) => ({ value, label })),
            ];
        },
    },
    { key: 'date_from', type: 'text' as const, placeholder: 'From (YYYY-MM-DD)' },
    { key: 'date_to', type: 'text' as const, placeholder: 'To (YYYY-MM-DD)' },
    {
        key: 'sort_by',
        type: 'select' as const,
        get options() {
            return Object.entries(props.sort_fields).map(([value, label]) => ({ value, label: `Sort by ${label}` }));
        },
    },
    {
        key: 'sort_direction',
        type: 'select' as const,
        options: [
            { value: 'asc', label: 'Ascending' },
            { value: 'desc', label: 'Descending' },
        ],
    },
];

function applyFilters(): void {
    router.get(index().url, filters.value, { preserveState: true, preserveScroll: true, replace: true });
}

function canExport(): boolean {
    return filters.value.date_from !== '' && filters.value.date_to !== '';
}
</script>

<template>
    <Head title="Audit trail" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Audit trail"
                :can-create="false"
                :export-href="canExport() ? auditTrail.export({ query: filters }).url : undefined"
                :can-export="permissions_meta.can_export && canExport()"
            />

            <FilterBar v-model="filters" :fields="filterFields" @change="applyFilters" />

            <AuditTrailTable :logs="logs.data" />

            <Pagination :meta="logs.meta" :links="logs.links" resource-label="audit entries" />
        </div>
    </div>
</template>