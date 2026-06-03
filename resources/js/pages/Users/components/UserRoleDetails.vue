<script setup lang="ts">
import type { User } from '@/types';

interface Props {
    user: User;
}

defineProps<Props>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-grey-900 text-lg leading-6 font-medium">
                Role Details
            </h3>
        </div>
        <div class="border-grey-200 border-t">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-grey-500 text-sm font-medium">Role</dt>
                    <dd
                        class="text-grey-900 mt-1 text-sm capitalize sm:col-span-2 sm:mt-0"
                    >
                        {{ user.role.replace('_', ' ') }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
