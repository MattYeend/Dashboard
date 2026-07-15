<script setup lang="ts">
import type { ApiToken } from '@/types';
import ApiTokenRow from './ApiTokenRow.vue';

defineProps<{
    tokens: ApiToken[];
    abilities: Record<string, string>;
}>();
</script>

<template>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Abilities</th>
                <th>Last used</th>
                <th>Expires</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <ApiTokenRow
                v-for="token in tokens"
                :key="token.id"
                :token="token"
                :abilities="abilities"
            />
        </tbody>
    </table>
</template>
