<script setup lang="ts">
import type { Tag } from '@/types';

defineProps<{
    tag: Tag;
}>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-300">
                Basic Details
            </h3>
        </div>
        <div class="border-t border-gray-500">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Name</dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        {{ tag.name }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Slug</dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        {{ tag.slug ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
