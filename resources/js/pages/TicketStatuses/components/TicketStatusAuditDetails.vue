<script setup lang="ts">
import type { TicketStatus } from '@/types';

interface Props {
    ticketStatus: TicketStatus;
}

defineProps<Props>();

function formatDateTime(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString();
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Audit details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Created by</dt>
                <dd class="text-sm">
                    {{ ticketStatus.creator?.name ?? 'System' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Created at</dt>
                <dd class="text-sm">
                    {{ formatDateTime(ticketStatus.created_at) }}
                </dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Last updated by</dt>
                <dd class="text-sm">
                    {{ ticketStatus.updater?.name ?? 'System' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Last updated at</dt>
                <dd class="text-sm">
                    {{ formatDateTime(ticketStatus.updated_at) }}
                </dd>
            </div>

            <template v-if="ticketStatus.deleted_at">
                <div>
                    <dt class="text-xs text-gray-400">Deleted by</dt>
                    <dd class="text-sm">
                        {{ ticketStatus.deleter?.name ?? 'System' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Deleted at</dt>
                    <dd class="text-sm">
                        {{ formatDateTime(ticketStatus.deleted_at) }}
                    </dd>
                </div>
            </template>

            <template v-if="ticketStatus.restored_at">
                <div>
                    <dt class="text-xs text-gray-400">Restored by</dt>
                    <dd class="text-sm">
                        {{ ticketStatus.restorer?.name ?? 'System' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Restored at</dt>
                    <dd class="text-sm">
                        {{ formatDateTime(ticketStatus.restored_at) }}
                    </dd>
                </div>
            </template>
        </dl>
    </div>
</template>
