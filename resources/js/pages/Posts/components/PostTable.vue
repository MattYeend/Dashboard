<script setup lang="ts">
import DOMPurify from 'dompurify';
import BulkActionButton from '@/components/table/BulkActionButton.vue';
import ResourceTable from '@/components/table/ResourceTable.vue';
import type { ResourceTableColumn } from '@/components/table/ResourceTable.vue';
import RowActions from '@/components/table/RowActions.vue';
import { formatDate, truncate } from '@/lib/formatters';
import { edit as postsEdit, show as postsShow } from '@/routes/posts';
import type { Post } from '@/types';

defineProps<{
    posts: Post[];
}>();

const selected = defineModel<Array<number | string>>('selected', {
    required: true,
});

const emit = defineEmits<{
    delete: [id: number];
    bulkDelete: [ids: Array<number | string>];
}>();

function stripHtml(value: string | null | undefined): string {
    if (!value) {
        return '';
    }

    // ALLOWED_TAGS: [] strips all HTML, leaving plain text content only
    return DOMPurify.sanitize(value, { ALLOWED_TAGS: [], ALLOWED_ATTR: [] });
}

function truncatePlainText(
    value: string | null | undefined,
    length = 30,
): string {
    return truncate(stripHtml(value), length);
}

const columns: ResourceTableColumn[] = [
    { key: 'title', label: 'Title' },
    { key: 'description', label: 'Description' },
    { key: 'tags', label: 'Tags' },
    { key: 'created_at', label: 'Created' },
];
</script>

<template>
    <ResourceTable
        v-model:selected="selected"
        :rows="posts"
        :columns="columns"
        row-key="id"
        selectable
        empty-message="No posts found."
    >
        <template #bulk-actions="{ selected: selection }">
            <BulkActionButton @click="emit('bulkDelete', selection)">
                Delete selected
            </BulkActionButton>
        </template>

        <template #cell-description="{ row }">
            {{ truncatePlainText(row.description, 30) }}
        </template>

        <template #cell-tags="{ row }">
            <span v-if="!row.tags?.length">-</span>
            <span v-else>{{ row.tags.map((tag) => tag.name).join(', ') }}</span>
        </template>

        <template #cell-created_at="{ row }">
            {{ formatDate(row.created_at) }}
        </template>

        <template #actions="{ row }">
            <RowActions
                :show-href="postsShow.url(row.id)"
                :edit-href="postsEdit.url(row.id)"
                @delete="emit('delete', row.id)"
            />
        </template>
    </ResourceTable>
</template>
