<script setup lang="ts">
import type { Company } from '@/types';

interface Props {
    company: Company;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Name</dt>
                <dd class="text-sm">{{ company.name }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Slug</dt>
                <dd class="text-sm">{{ company.slug ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Industry</dt>
                <dd class="text-sm">{{ company.industry?.title ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Account manager</dt>
                <dd class="text-sm">
                    {{ company.account_manager?.name ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>
</template>