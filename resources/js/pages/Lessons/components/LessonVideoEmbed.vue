<script setup lang="ts">
defineProps<{
    embedUrl: string | null;
    title: string;
}>();
</script>

<template>
    <div v-if="embedUrl" class="aspect-video w-full border border-gray-500">
        <iframe
            :src="embedUrl"
            :title="title"
            class="size-full"
            loading="lazy"
            referrerpolicy="no-referrer"
            sandbox="allow-scripts allow-same-origin allow-presentation"
            allowfullscreen
        />
    </div>
</template>
