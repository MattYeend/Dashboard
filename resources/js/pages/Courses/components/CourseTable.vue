<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as coursesEdit, show as coursesShow } from '@/routes/courses';
import type { Course } from '@/types';

defineProps<{
    courses: Course[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

const columns: ResourceTableColumn[] = [
    { key: 'title', label: 'Title' },
    { key: 'difficulty_label', label: 'Difficulty' },
    { key: 'estimated_duration_minutes', label: 'Duration (minutes)' },
    { key: 'instructor_name', label: 'Instructor' },
    { key: 'published_at', label: 'Published' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="courses"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No courses found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="coursesShow.url(row.id)"
                :edit-href="coursesEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>