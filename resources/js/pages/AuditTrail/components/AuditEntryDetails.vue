<script setup lang="ts">
import type { AuditLogEntry } from '@/types';

defineProps<{
    log: AuditLogEntry;
}>();

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB');
}
</script>

<template>
    <section class="rounded-lg border p-4">
        <h2 class="mb-3 text-sm font-medium">Details</h2>
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <dt class="text-xs">Action</dt>
                <dd class="text-sm">{{ log.action_label }}</dd>
            </div>
            <div>
                <dt class="text-xs">Date</dt>
                <dd class="text-sm">{{ formatDateTime(log.created_at) }}</dd>
            </div>
            <div>
                <dt class="text-xs">Performed by</dt>
                <dd class="text-sm">{{ log.logged_in_user?.name ?? 'System' }}</dd>
            </div>
            <div>
                <dt class="text-xs">Related to</dt>
                <dd class="text-sm">{{ log.related_to_user?.name ?? 'None' }}</dd>
            </div>
        </dl>
    </section>
</template>