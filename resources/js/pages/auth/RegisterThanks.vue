<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { home } from '@/routes';
</script>

<template>
    <Head title="Thank you" />
    <div class="mx-auto max-w-md py-16 text-center">
        <h1 class="text-xl font-semibold">Thanks for registering your interest</h1>
        <p class="mt-2 text-sm text-gray-400">
            We've received your details and will be in touch shortly.
        </p>
        <Link :href="home()" class="mt-6 inline-block underline">
            Back to home
        </Link>
    </div>
</template>
