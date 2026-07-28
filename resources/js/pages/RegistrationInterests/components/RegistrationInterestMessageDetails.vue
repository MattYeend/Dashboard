<script setup lang="ts">
import type { RegistrationInterest } from '@/types';

interface Props {
    interest: RegistrationInterest;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Message</h2>

        <p class="text-sm">
            {{ interest.message ?? 'No message provided.' }}
        </p>
    </div>
</template>
