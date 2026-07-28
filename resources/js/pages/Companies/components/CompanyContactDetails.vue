<script setup lang="ts">
import type { Company } from '@/types';

interface Props {
    company: Company;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Contact details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Email</dt>
                <dd class="text-sm">{{ company.email ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Phone</dt>
                <dd class="text-sm">{{ company.phone ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Website</dt>
                <dd class="text-sm">
                    <a
                        v-if="company.website"
                        :href="company.website"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="underline"
                    >
                        {{ company.website }}
                    </a>
                    <span v-else>-</span>
                </dd>
            </div>
        </dl>
    </div>
</template>
