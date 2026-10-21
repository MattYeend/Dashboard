<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import FilterBar from '@/components/table/FilterBar.vue';
import IndexHeader from '@/components/table/IndexHeader.vue';
import Pagination from '@/components/table/Pagination.vue';
import { useBulkAction, useRowAction } from '@/composables/useConfirmedAction';
import { useIndexFilters } from '@/composables/useIndexFilters';
import {
    create as questionsCreate,
    destroy as questionsDestroy,
    exportMethod as questionsExport,
    index as questionsIndex,
} from '@/routes/questions';
import questionsBulk from '@/routes/questions/bulk';
import type {
    Pagination as PaginationMeta,
    PaginationLink,
    Question,
    QuestionPermissionsMeta,
} from '@/types';
import QuestionTable from './components/QuestionTable.vue';

interface Props {
    questions: {
        data: Question[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: QuestionPermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => questionsIndex.url(),
    searchPlaceholder: 'Search questions…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'position',
    defaultSortDirection: 'asc',
});

const selectedIds = ref<Array<number | string>>([]);

const clearSelection = (): void => {
    selectedIds.value = [];
};

const deleteAction = useRowAction((id, options) =>
    router.delete(questionsDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(questionsBulk.delete.url(), { ids }, options),
    clearSelection,
);

const bulkRestoreAction = useBulkAction(
    (ids, options) =>
        router.post(questionsBulk.restore.url(), { ids }, options),
    clearSelection,
);
</script>

<template>
    <Head title="Questions" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Questions"
                :create-href="questionsCreate.url()"
                create-label="Add Question"
                :can-create="permissions_meta.can_create"
                :export-href="questionsExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <QuestionTable
                v-model:selected="selectedIds"
                :questions="questions.data"
                :can-restore="permissions_meta.can_restore"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
                @bulk-restore="bulkRestoreAction.request"
            />

            <Pagination
                :meta="questions.meta"
                :links="questions.links"
                resource-label="questions"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete question"
            description="This question will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete questions"
            :description="`${bulkDeleteAction.ids.length} question(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkRestoreAction.open"
            title="Restore questions"
            :description="`${bulkRestoreAction.ids.length} question(s) will be restored.`"
            confirm-label="Restore"
            :processing="bulkRestoreAction.processing"
            @confirm="bulkRestoreAction.confirm"
        />
    </div>
</template>
