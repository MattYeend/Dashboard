<script setup lang="ts">
import type { EmailTemplate } from '@/types';

interface Props {
    emailTemplate: EmailTemplate;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Name</dt>
                <dd class="text-sm">
                    {{ emailTemplate.name }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Subject</dt>
                <dd class="text-sm">
                    {{ emailTemplate.subject }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Preheader</dt>
                <dd class="text-sm">
                    {{ emailTemplate.preheader ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Active</dt>
                <dd class="text-sm">
                    {{ emailTemplate.is_active ? 'Yes' : 'No' }}
                </dd>
            </div>
        </dl>
    </div>
</template>