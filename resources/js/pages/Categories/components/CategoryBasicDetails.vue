<script setup lang="ts">
import type { Category } from '@/types';

interface Props {
    category: Category;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Category details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Name</dt>
                <dd class="text-sm">{{ category.name }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Slug</dt>
                <dd class="text-sm">{{ category.slug }}</dd>
            </div>

            <div>
                <dt class="text-xs text-gray-400">Parent category</dt>
                <dd class="text-sm">{{ category.parent?.name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Description</dt>
                <dd class="text-sm">{{ category.description ?? '-' }}</dd>
            </div>
        </dl>
    </div>
</template>