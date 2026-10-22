<script setup lang="ts">
import type { Answer } from '@/types';

defineProps<{ answer: Answer }>();

function format(value: string | null): string {
    return value ? new Date(value).toLocaleString('en-GB') : 'N/A';
}
</script>

<template>
    <section class="grid gap-2">
        <h2 class="text-lg font-medium">History</h2>
        <dl class="grid gap-1 text-sm sm:grid-cols-2">
            <dt class="font-medium">Created</dt>
            <dd>
                {{ format(answer.created_at) }} by
                {{ answer.creator?.name ?? 'N/A' }}
            </dd>
            <dt class="font-medium">Updated</dt>
            <dd>
                {{ format(answer.updated_at) }} by
                {{ answer.updater?.name ?? 'N/A' }}
            </dd>
            <template v-if="answer.deleted_at">
                <dt class="font-medium">Deleted</dt>
                <dd>
                    {{ format(answer.deleted_at) }} by
                    {{ answer.deleter?.name ?? 'N/A' }}
                </dd>
            </template>
            <template v-if="answer.restored_at">
                <dt class="font-medium">Restored</dt>
                <dd>
                    {{ format(answer.restored_at) }} by
                    {{ answer.restorer?.name ?? 'N/A' }}
                </dd>
            </template>
        </dl>
    </section>
</template>
