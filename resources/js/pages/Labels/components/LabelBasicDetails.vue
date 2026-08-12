<script setup lang="ts">
import type { Label } from '@/types';

interface Props {
    label: Label;
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
                    {{ label.name }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Slug</dt>
                <dd class="font-mono text-sm">
                    {{ label.slug }}
                </dd>
            </div>
        </dl>
    </div>
</template>
