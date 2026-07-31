<script setup lang="ts">
import { GitBranch } from 'lucide-vue-next';
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    total: number;
    won: number;
    lost: number;
}>();
</script>

<template>
    <StatCard label="Pipelines" :value="total" :icon="GitBranch" />
    <StatCard label="Pipelines won" :value="won" :hint="`${lost} lost`" />
</template>
