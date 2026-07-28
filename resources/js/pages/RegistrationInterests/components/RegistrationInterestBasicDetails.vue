<script setup lang="ts">
import type { RegistrationInterest } from '@/types';

interface Props {
    interest: RegistrationInterest;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Name</dt>
                <dd class="text-sm">
                    {{ interest.name }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Email</dt>
                <dd class="text-sm">
                    {{ interest.email }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Phone</dt>
                <dd class="text-sm">
                    {{ interest.phone ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Company</dt>
                <dd class="text-sm">
                    {{ interest.company ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>
</template>
