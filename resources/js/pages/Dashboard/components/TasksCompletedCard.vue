<script setup lang="ts">
import { CheckCircle2 } from 'lucide-vue-next';
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    completed: number;
}>();
</script>

<template>
    <StatCard
        label="Tasks completed"
        :value="completed"
        :icon="CheckCircle2"
        hint="Assigned to you"
    />
</template>
