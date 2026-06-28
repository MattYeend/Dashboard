<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props {
    title: string;
    createHref: string;
    createLabel: string;
    canCreate?: boolean;
}

withDefaults(defineProps<Props>(), {
    canCreate: true,
});
</script>

<template>
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-300">{{ title }}</h1>
        <Link
            v-if="canCreate"
            :href="createHref"
            class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm"
        >
            {{ createLabel }}
        </Link>
    </div>
</template>
