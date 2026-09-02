<script setup lang="ts">
import TagBadge from '@/components/TagBadge.vue';
import type { Contact } from '@/types';

interface Props {
    contact: Contact;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-md border border-gray-500 p-4">
        <dl>
            <dt class="text-sm font-medium text-gray-400">Tags</dt>
            <dd class="mt-1 text-sm text-gray-300">
                <span v-if="!contact.tags?.length">-</span>
                <span v-else class="flex flex-wrap gap-2">
                    <TagBadge
                        v-for="tag in contact.tags"
                        :key="tag.id"
                        :name="tag.name"
                    />
                </span>
            </dd>
        </dl>
    </div>
</template>