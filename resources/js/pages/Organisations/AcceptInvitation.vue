<script setup lang="ts">
interface Props {
    message: string;
}

defineProps<Props>();
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-md px-4 text-center sm:px-6 lg:px-8">
            <p class="text-sm text-gray-300">{{ message }}</p>
        </div>
    </div>
</template>
