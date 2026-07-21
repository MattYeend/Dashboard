<script setup lang="ts">
import DOMPurify from 'dompurify';
import { computed } from 'vue';
import type { Post } from '@/types';

const props = defineProps<{ post: Post }>();

const sanitisedDescription = computed(() =>
    DOMPurify.sanitize(props.post.description),
);
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-300">
                Post Details
            </h3>
        </div>
        <div class="border-t border-gray-500">
            <dl>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Title</dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        {{ post.title }}
                    </dd>
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">
                        Description
                    </dt>
                    <dd
                        class="prose prose-sm mt-1 max-w-none text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                        v-html="sanitisedDescription"
                    />
                </div>
                <div class="px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-400">Image</dt>
                    <dd
                        class="mt-1 text-sm text-gray-300 sm:col-span-2 sm:mt-0"
                    >
                        <img
                            v-if="post.image"
                            :src="post.image"
                            :alt="post.title"
                            class="h-24 w-24 rounded-md object-cover"
                        />
                        <span v-else>-</span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
