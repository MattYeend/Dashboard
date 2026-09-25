<script setup lang="ts">
import type { AuditLogEntry } from '@/types';

defineProps<{
    log: AuditLogEntry;
}>();
</script>

<template>
    <section class="rounded-lg border p-4">
        <h2 class="mb-3 text-sm font-medium">Integrity</h2>

        <p v-if="!log.is_sealed" class="text-sm">
            This entry has not been sealed into the hash chain yet. This
            normally takes under a minute.
        </p>

        <dl v-else class="grid grid-cols-1 gap-3">
            <div>
                <dt class="text-xs">Sequence</dt>
                <dd class="text-sm">{{ log.sequence }}</dd>
            </div>
            <div>
                <dt class="text-xs">Hash</dt>
                <dd class="text-xs break-all">{{ log.hash }}</dd>
            </div>
            <div>
                <dt class="text-xs">Previous hash</dt>
                <dd class="text-xs break-all">
                    {{ log.previous_hash ?? 'Start of chain' }}
                </dd>
            </div>
        </dl>
    </section>
</template>
