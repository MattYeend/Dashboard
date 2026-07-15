<script setup lang="ts">
import type { ApiToken } from '@/types';
import ApiTokenRow from './ApiTokenRow.vue';

defineProps<{
    tokens: ApiToken[];
    abilities: Record<string, string>;
}>();

const emit = defineEmits<{
    revoke: [token: ApiToken];
}>();
</script>

<template>
    <table class="min-w-full divide-y divide-gray-800">
        <thead>
            <tr>
                <th
                    class="px-4 py-3 text-left text-sm font-medium text-gray-300"
                >
                    Name
                </th>
                <th
                    class="px-4 py-3 text-left text-sm font-medium text-gray-300"
                >
                    Abilities
                </th>
                <th
                    class="px-4 py-3 text-left text-sm font-medium text-gray-300"
                >
                    Last used
                </th>
                <th
                    class="px-4 py-3 text-left text-sm font-medium text-gray-300"
                >
                    Expires
                </th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            <ApiTokenRow
                v-for="token in tokens"
                :key="token.id"
                :token="token"
                :abilities="abilities"
                @revoke="emit('revoke', $event)"
            />
        </tbody>
    </table>
</template>
