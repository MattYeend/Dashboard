<script setup lang="ts">
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { edit as quizzesEdit, show as quizzesShow } from '@/routes/quizzes';
import type { Quiz } from '@/types';

defineProps<{
    quizzes: Quiz[];
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
    { key: 'course_title', label: 'Course' },
    { key: 'lesson_title', label: 'Lesson' },
    { key: 'questions_count', label: 'Questions' },
    { key: 'pass_mark_percentage', label: 'Pass mark (%)' },
    { key: 'published_at', label: 'Published' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="quizzes"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No quizzes found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="quizzesShow.url(row.id)"
                :edit-href="quizzesEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
