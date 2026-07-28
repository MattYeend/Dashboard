<script setup lang="ts">
import type { Company } from '@/types';

interface Props {
    company: Company;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">
            Registration details
        </h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Registration number</dt>
                <dd class="text-sm">
                    {{ company.registration_number ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">VAT number</dt>
                <dd class="text-sm">{{ company.vat_number ?? '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Employee count</dt>
                <dd class="text-sm">{{ company.employee_count ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Founded year</dt>
                <dd class="text-sm">{{ company.founded_year ?? '-' }}</dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-xs text-gray-400">Description</dt>
                <dd class="text-sm">{{ company.description ?? '-' }}</dd>
            </div>
        </dl>
    </div>
</template>
