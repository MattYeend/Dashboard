<script setup lang="ts">
import type { Attachment } from '@/types';
import { router } from '@inertiajs/vue3';

defineProps<{
    attachments: Attachment[];
    canDelete: boolean;
}>();

function destroy(attachment: Attachment) {
    router.delete(`/attachments/${attachment.id}`, { preserveScroll: true });
}
</script>

<template>
    <ul class="divide-y divide-gray-500">
        <li
            v-for="attachment in attachments"
            :key="attachment.id"
            class="flex items-center justify-between py-2"
        >
            <div>
                <a :href="attachment.download_url" class="text-sm text-gray-300 hover:underline">
                    {{ attachment.original_filename }}
                </a>
                <p class="text-xs text-gray-400">
                    {{ attachment.size_human }} · uploaded by {{ attachment.creator?.name ?? 'System' }}
                </p>
            </div>
            <button
                v-if="canDelete"
                type="button"
                class="text-xs text-red-400 hover:underline"
                @click="destroy(attachment)"
            >
                Delete
            </button>
        </li>
    </ul>
</template>
