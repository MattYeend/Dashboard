<script setup lang="ts">
import type { RegistrationInterest } from '@/types';

defineProps<{
    interest: RegistrationInterest;
}>();
</script>

<template>
    <div class="border-gray-500 border-b pb-6">
        <h2 class="mb-3 text-sm font-medium text-gray-400">Details</h2>
        <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
            <dt class="text-gray-400">Name</dt>
            <dd class="text-gray-300">{{ interest.name }}</dd>

            <dt class="text-gray-400">Email</dt>
            <dd class="text-gray-300">{{ interest.email }}</dd>

            <dt class="text-gray-400">Phone</dt>
            <dd class="text-gray-300">{{ interest.phone ?? '-' }}</dd>

            <dt class="text-gray-400">Company</dt>
            <dd class="text-gray-300">{{ interest.company ?? '-' }}</dd>
        </dl>
    </div>
</template>
