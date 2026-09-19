<script setup lang="ts">
import type { Permission } from '@/types';

defineProps<{ permission: Permission }>();

function formatDate(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Audit details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Created by</dt>
                <dd class="text-sm">
                    {{ permission.creator?.name ?? 'System' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Updated by</dt>
                <dd class="text-sm">
                    {{ permission.updater?.name ?? 'System' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Deleted by</dt>
                <dd class="text-sm">
                    {{ permission.deleter?.name ?? 'System' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Restored</dt>
                <dd class="text-sm">
                    {{
                        permission.restorer
                            ? `${permission.restorer.name} on ${formatDate(permission.restored_at)}`
                            : '-'
                    }}
                </dd>
            </div>
        </dl>
    </div>
</template>
