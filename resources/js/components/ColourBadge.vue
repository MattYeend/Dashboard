<script setup lang="ts">
withDefaults(
    defineProps<{
        label: string;
        backgroundColour?: string | null;
        textColour?: string | null;
    }>(),
    { backgroundColour: null, textColour: null },
);
</script>

<template>
    <span
        class="rounded px-2 py-0.5 text-xs font-medium"
        :style="{
            backgroundColor: backgroundColour ?? '#e2e8f0',
            color: textColour ?? '#1a202c',
        }"
    >
        {{ label }}
    </span>
</template>
