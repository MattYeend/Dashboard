<script setup lang="ts">
import type { ApiToken } from '@/types';

defineProps<{
    token: ApiToken;
    abilities: Record<string, string>;
}>();

const emit = defineEmits<{
    revoke: [token: ApiToken];
}>();
</script>

<template>
    <tr>
        <td>{{ token.name }}</td>
        <td>{{ token.abilities.map((a) => abilities[a] ?? a).join(', ') }}</td>
        <td>{{ token.last_used_at ?? 'Never' }}</td>
        <td>{{ token.expires_at ?? 'Never' }}</td>
        <td>
            <button
                type="button"
                class="text-red-600 hover:text-red-900"
                @click="emit('revoke', token)"
            >
                Revoke
            </button>
        </td>
    </tr>
</template>
