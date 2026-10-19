<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as lessonsEdit, show as lessonsShow } from '@/routes/lessons';
import type { Lesson } from '@/types';

defineProps<{
    lessons: Lesson[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'position', label: 'Position' },
    { key: 'title', label: 'Title' },
    { key: 'course.title', label: 'Course' },
    { key: 'duration_minutes', label: 'Duration (minutes)' },
    { key: 'published_at', label: 'Published' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="lessons"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No lessons found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="lessonsShow.url(row.id)"
                :edit-href="lessonsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
