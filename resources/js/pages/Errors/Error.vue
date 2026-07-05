<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { errorMessages, defaultErrorMessage } from '@/lib/errorMessages';

const props = defineProps<{
    status: number;
}>();

const message = computed(
    () => errorMessages[props.status] ?? defaultErrorMessage,
);
</script>

<template>
    <Head :title="`${status} - ${message.title}`" />

    <div
        class="flex min-h-screen flex-col items-center justify-center px-4 text-center"
    >
        <p class="text-sm font-semibold text-gray-500">Error {{ status }}</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">
            {{ message.title }}
        </h1>
        <p class="mt-4 text-base text-gray-600">
            {{ message.description }}
        </p>
        <div class="mt-8">
            <Link href="/" class="text-sm font-semibold underline">
                Go back home
            </Link>
        </div>
    </div>
</template>
