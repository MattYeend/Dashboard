<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { ApiToken } from '@/types';

const props = defineProps<{
    token: ApiToken;
    abilities: Record<string, string>;
}>();

const revoke = () => {
    if (confirm(`Revoke token "${props.token.name}"? This cannot be undone.`)) {
        router.delete(route('api-tokens.destroy', props.token.id));
    }
};
</script>

<template>
    <tr>
        <td>{{ token.name }}</td>
        <td>{{ token.abilities.map((a) => abilities[a] ?? a).join(', ') }}</td>
        <td>{{ token.last_used_at ?? 'Never' }}</td>
        <td>{{ token.expires_at ?? 'Never' }}</td>
        <td>
            <button type="button" @click="revoke">Revoke</button>
        </td>
    </tr>
</template>
