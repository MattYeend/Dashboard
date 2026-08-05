<script setup lang="ts">
import { BarChart3 } from 'lucide-vue-next';
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    label: string;
    value: number;
}>();
</script>

<template>
    <StatCard :label="label" :value="value" :icon="BarChart3" />
</template>
