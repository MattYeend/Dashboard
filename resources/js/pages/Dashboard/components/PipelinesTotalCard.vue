<script setup lang="ts">
import { GitBranch } from 'lucide-vue-next';
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    total: number;
}>();
</script>

<template>
    <StatCard label="Pipelines" :value="total" :icon="GitBranch" />
</template>