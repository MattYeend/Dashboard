<script setup lang="ts">
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    won: number;
    lost: number;
}>();
</script>

<template>
    <StatCard label="Deals won" :value="won" :hint="`${lost} lost`" />
</template>