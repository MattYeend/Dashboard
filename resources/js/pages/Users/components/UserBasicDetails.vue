<script setup lang="ts">
import type { User } from '@/types';

interface Props {
    user: User;
    availableLocales: Record<string, string>;
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
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Name</dt>
                <dd class="text-sm">
                    {{ user.name }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Email address</dt>
                <dd class="text-sm">
                    {{ user.email }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Email verified</dt>
                <dd class="text-sm">
                    {{
                        user.email_verified_at
                            ? formatDateTime(user.email_verified_at)
                            : 'Not verified'
                    }}
                </dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Locale</dt>
                <dd class="text-sm">
                    {{ availableLocales[user.locale] ?? user.locale }}
                </dd>
            </div>
        </dl>
    </div>
</template>
