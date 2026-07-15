<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ApiToken } from '@/types';
import ApiTokenForm from './components/ApiTokenForm.vue';
import ApiTokenList from './components/ApiTokenList.vue';
import NewTokenReveal from './components/NewTokenReveal.vue';

defineProps<{
    tokens: ApiToken[];
    abilities: Record<string, string>;
}>();

const page = usePage<{ flash: { plainTextToken?: string } }>();
const plainTextToken = computed(() => page.props.flash?.plainTextToken ?? null);
</script>

<template>
    <div class="space-y-6">
        <NewTokenReveal v-if="plainTextToken" :token="plainTextToken" />

        <ApiTokenForm :abilities="abilities" />

        <ApiTokenList :tokens="tokens" :abilities="abilities" />
    </div>
</template>
