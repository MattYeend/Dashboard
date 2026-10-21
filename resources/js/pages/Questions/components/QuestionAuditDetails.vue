<script setup lang="ts">
import type { Question } from '@/types';

defineProps<{ question: Question }>();

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
                {{ format(question.created_at) }} by
                {{ question.creator?.name ?? 'N/A' }}
            </dd>
            <dt class="font-medium">Updated</dt>
            <dd>
                {{ format(question.updated_at) }} by
                {{ question.updater?.name ?? 'N/A' }}
            </dd>
            <template v-if="question.deleted_at">
                <dt class="font-medium">Deleted</dt>
                <dd>
                    {{ format(question.deleted_at) }} by
                    {{ question.deleter?.name ?? 'N/A' }}
                </dd>
            </template>
            <template v-if="question.restored_at">
                <dt class="font-medium">Restored</dt>
                <dd>
                    {{ format(question.restored_at) }} by
                    {{ question.restorer?.name ?? 'N/A' }}
                </dd>
            </template>
        </dl>
    </section>
</template>
