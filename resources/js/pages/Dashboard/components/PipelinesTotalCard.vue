<script setup lang="ts">
import { GitBranch } from '@lucide/vue';
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    total: number;
}>();
</script>

<template>
    <StatCard label="Pipelines" :value="total" :icon="GitBranch" />
</template>
