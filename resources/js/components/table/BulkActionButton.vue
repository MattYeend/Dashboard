<script setup lang="ts">
withDefaults(
    defineProps<{
        variant?: 'danger' | 'neutral';
    }>(),
    { variant: 'danger' },
);
</script>

<template>
    <button
        type="button"
        class="rounded-md border px-3 py-1.5 text-sm font-medium"
        :class="
            variant === 'danger'
                ? 'border-red-600 text-red-500 hover:text-red-400'
                : 'border-gray-500 text-gray-300 hover:text-white'
        "
    >
        <slot />
    </button>
</template>
