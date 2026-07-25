<script setup lang="ts">
import type { RegistrationInterest } from '@/types';

defineProps<{
    interest: RegistrationInterest;
}>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-300">Message</h3>
        </div>
        <div class="border-t border-gray-500">
            <div class="px-4 py-4 sm:px-6">
                <p class="text-sm text-gray-300">
                    {{ interest.message ?? 'No message provided.' }}
                </p>
            </div>
        </div>
    </div>
</template>
