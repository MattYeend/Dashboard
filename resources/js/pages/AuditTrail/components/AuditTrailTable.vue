<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { show } from '@/routes/audit-trail';
import type { AuditLogEntry } from '@/types';

defineProps<{
    logs: AuditLogEntry[];
}>();

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB');
}
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b text-xs">
                    <th class="px-3 py-2">Date</th>
                    <th class="px-3 py-2">Action</th>
                    <th class="px-3 py-2">Performed by</th>
                    <th class="px-3 py-2">Related to</th>
                    <th class="px-3 py-2">Sealed</th>
                    <th class="px-3 py-2"><span class="sr-only">View</span></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="log in logs" :key="log.id" class="border-b">
                    <td class="px-3 py-2">
                        {{ formatDateTime(log.created_at) }}
                    </td>
                    <td class="px-3 py-2">{{ log.action_label }}</td>
                    <td class="px-3 py-2">
                        {{ log.logged_in_user?.name ?? 'System' }}
                    </td>
                    <td class="px-3 py-2">
                        {{ log.related_to_user?.name ?? '' }}
                    </td>
                    <td class="px-3 py-2">
                        {{ log.is_sealed ? 'Yes' : 'Pending' }}
                    </td>
                    <td class="px-3 py-2">
                        <Link
                            :href="show({ log: log.id }).url"
                            class="underline"
                            >View</Link
                        >
                    </td>
                </tr>
                <tr v-if="logs.length === 0">
                    <td colspan="6" class="px-3 py-6 text-center">
                        No entries match these filters.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
