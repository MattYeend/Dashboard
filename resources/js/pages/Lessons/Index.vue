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
    create as lessonsCreate,
    destroy as lessonsDestroy,
    exportMethod as lessonsExport,
    index as lessonsIndex,
} from '@/routes/lessons';
import lessonsBulk from '@/routes/lessons/bulk';
import type {
    Lesson,
    Pagination as PaginationMeta,
    PaginationLink,
    PermissionsMeta,
} from '@/types';
import LessonTable from './components/LessonTable.vue';

interface Props {
    lessons: {
        data: Lesson[];
        links: PaginationLink[];
        meta: PaginationMeta;
    };
    permissions_meta: PermissionsMeta;
    sort_fields: Record<string, string>;
    trash_filters: Record<string, string>;
}

const props = defineProps<Props>();

const { filters, filterFields, applyFilters } = useIndexFilters({
    url: () => lessonsIndex.url(),
    searchPlaceholder: 'Search lessons…',
    sortFields: () => props.sort_fields,
    trashFilters: () => props.trash_filters,
    defaultSortBy: 'position',
});

const selectedIds = ref<Array<number | string>>([]);

const deleteAction = useRowAction((id, options) =>
    router.delete(lessonsDestroy.url(id), options),
);

const bulkDeleteAction = useBulkAction(
    (ids, options) => router.post(lessonsBulk.delete.url(), { ids }, options),
    () => {
        selectedIds.value = [];
    },
);
</script>

<template>
    <Head title="Lessons" />

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <IndexHeader
                title="Lessons"
                :create-href="lessonsCreate.url()"
                create-label="Add Lesson"
                :can-create="permissions_meta.can_create"
                :export-href="lessonsExport.url()"
                :can-export="permissions_meta.can_export"
            />

            <FilterBar
                v-model="filters"
                :fields="filterFields"
                @change="applyFilters"
            />

            <LessonTable
                v-model:selected="selectedIds"
                :lessons="lessons.data"
                @delete="deleteAction.request"
                @bulk-delete="bulkDeleteAction.request"
            />

            <Pagination
                :meta="lessons.meta"
                :links="lessons.links"
                resource-label="lessons"
            />
        </div>

        <ConfirmDialog
            v-model:open="deleteAction.open"
            title="Delete lesson"
            description="This lesson will be moved to trash."
            confirm-label="Delete"
            :processing="deleteAction.processing"
            @confirm="deleteAction.confirm"
        />

        <ConfirmDialog
            v-model:open="bulkDeleteAction.open"
            title="Delete lessons"
            :description="`${bulkDeleteAction.ids.length} lesson(s) will be moved to trash.`"
            confirm-label="Delete"
            :processing="bulkDeleteAction.processing"
            @confirm="bulkDeleteAction.confirm"
        />
    </div>
</template>
