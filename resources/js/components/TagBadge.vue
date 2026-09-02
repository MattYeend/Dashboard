<script setup lang="ts">
interface Props {
    name: string;
}

defineProps<Props>();
</script>

<template>
    <span
        class="rounded-full border border-gray-500 px-2 py-0.5 text-xs text-gray-300"
    >
        {{ name }}
    </span>
</template>
