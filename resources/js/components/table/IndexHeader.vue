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
    <div class="items-centre mb-4 flex justify-between">
        <h1 class="text-grey-900 text-2xl font-semibold">{{ title }}</h1>
        <Link
            v-if="canCreate"
            :href="createHref"
            class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm"
        >
            {{ createLabel }}
        </Link>
    </div>
</template>
