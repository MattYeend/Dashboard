<script setup lang="ts">
import TagPicker from '@/components/TagPicker.vue';

interface TagOption {
    id: number;
    name: string;
}

interface Props {
    tags: TagOption[];
    errors: Partial<Record<'tag_ids', string>>;
}

defineProps<Props>();

const tagIds = defineModel<number[]>('tagIds', { required: true });
</script>

<template>
    <TagPicker v-model:tag-ids="tagIds" :tags="tags" :errors="errors" />
</template>