<script setup lang="ts">
import type { Company } from '@/types';

defineProps<{
    company: Company;
}>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-300">
                Contact Details
            </h3>
        </div>
        <div class="border-t border-gray-500">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Email</dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        {{ company.email ?? '—' }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Phone</dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        {{ company.phone ?? '—' }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Website</dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        <a
                            v-if="company.website"
                            :href="company.website"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="underline"
                        >
                            {{ company.website }}
                        </a>
                        <span v-else>—</span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
