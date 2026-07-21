<script setup lang="ts">
import type { RegistrationInterest } from '@/types';

defineProps<{
    interest: RegistrationInterest;
}>();
</script>

<template>
    <div class="border-gray-500 border-b pb-6">
        <h2 class="mb-3 text-sm font-medium text-gray-400">Message</h2>
        <p class="text-sm text-gray-300">
            {{ interest.message ?? 'No message provided.' }}
        </p>
    </div>
</template>
