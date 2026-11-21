<script setup lang="ts">
import type { EmailTemplate } from '@/types';

interface Props {
    emailTemplate: EmailTemplate;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Content</h2>

        <dl class="grid grid-cols-1 gap-4">
            <div>
                <dt class="text-xs text-gray-400">HTML body</dt>
                <dd class="text-sm break-words whitespace-pre-wrap">
                    {{ emailTemplate.body_html }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Plain text body</dt>
                <dd class="text-sm break-words whitespace-pre-wrap">
                    {{ emailTemplate.body_text ?? '-' }}
                </dd>
            </div>
        </dl>
    </div>
</template>