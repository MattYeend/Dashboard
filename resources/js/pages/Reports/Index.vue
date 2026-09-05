<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/FilterBar.vue';
import IndexHeader from '@/components/IndexHeader.vue';
import Pagination from '@/components/Pagination.vue';
import ResourceTable from '@/components/ResourceTable.vue';
import { create as reportsCreate, show as reportsShow } from '@/routes/reports';
import { destroy as bulkDestroy } from '@/routes/reports/bulk';
import type { Pagination as PaginationType, Report, ReportPermissionsMeta } from '@/types';

defineProps<{
    reports: { data: Report[]; links: unknown; meta: PaginationType };
    permissions_meta: ReportPermissionsMeta;
}>();

const selected = ref<number[]>([]);
const confirmingBulkDelete = ref(false);

function confirmBulkDelete() {
    router.post(bulkDestroy().url, { ids: selected.value }, {
        onSuccess: () => {
            selected.value = [];
            confirmingBulkDelete.value = false;
        },
    });
}
</script>

<template>
    <div class="space-y-6">
        <IndexHeader
            title="Reports"
            :create-href="reportsCreate().url"
            create-label="Create Report"
            :can-create="permissions_meta.can_create"
        />

        <FilterBar />

        <ResourceTable
            v-model:selected="selected"
            :columns="[
                { key: 'title', label: 'Title' },
                { key: 'type_label', label: 'Covers' },
                { key: 'format', label: 'Format' },
                { key: 'is_scheduled', label: 'Scheduled' },
            ]"
            :rows="reports.data"
            selectable
        >
            <template #cell-title="{ row }">
                <a :href="reportsShow(row.id).url" class="text-gray-200 underline">{{ row.title }}</a>
            </template>
            <template #cell-is_scheduled="{ row }">
                {{ row.is_scheduled ? 'Yes' : 'No' }}
            </template>
            <template #bulk-actions>
                <button type="button" class="text-sm text-gray-200 underline" @click="confirmingBulkDelete = true">
                    Delete selected
                </button>
            </template>
        </ResourceTable>

        <Pagination :meta="reports.meta" :links="reports.links" resource-label="reports" />

        <ConfirmDialog
            v-model:open="confirmingBulkDelete"
            title="Delete selected reports?"
            @confirm="confirmBulkDelete"
        />
    </div>
</template>