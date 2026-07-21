<script setup lang="ts">
import type { RegistrationInterest } from '@/types';

defineProps<{
    interest: RegistrationInterest;
}>();
</script>

<template>
    <div>
        <h2 class="mb-3 text-sm font-medium text-gray-400">Audit</h2>
        <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
            <dt class="text-gray-400">Created</dt>
            <dd class="text-gray-300">
                {{ new Date(interest.created_at).toLocaleDateString('en-GB') }}
            </dd>

            <template v-if="interest.deleted_at">
                <dt class="text-gray-400">Deleted by</dt>
                <dd class="text-gray-300">{{ interest.deleter?.name ?? '-' }}</dd>

                <dt class="text-gray-400">Deleted</dt>
                <dd class="text-gray-300">
                    {{ new Date(interest.deleted_at).toLocaleDateString('en-GB') }}
                </dd>
            </template>

            <template v-if="interest.restored_at">
                <dt class="text-gray-400">Restored by</dt>
                <dd class="text-gray-300">{{ interest.restorer?.name ?? '-' }}</dd>

                <dt class="text-gray-400">Restored</dt>
                <dd class="text-gray-300">
                    {{ new Date(interest.restored_at).toLocaleDateString('en-GB') }}
                </dd>
            </template>
        </dl>
    </div>
</template>
