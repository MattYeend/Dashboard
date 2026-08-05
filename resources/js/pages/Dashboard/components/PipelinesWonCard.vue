<script setup lang="ts">
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    won: number;
    lost: number;
}>();
</script>

<template>
    <StatCard label="Pipelines won" :value="won" :hint="`${lost} lost`" />
</template>