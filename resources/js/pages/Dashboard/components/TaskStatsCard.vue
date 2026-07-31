<script setup lang="ts">
import { CheckCircle2, ListTodo } from 'lucide-vue-next';
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    completed: number;
    outstanding: number;
}>();
</script>

<template>
    <StatCard
        label="Tasks completed"
        :value="completed"
        :icon="CheckCircle2"
        hint="Assigned to you"
    />
    <StatCard
        label="Tasks outstanding"
        :value="outstanding"
        :icon="ListTodo"
        hint="Assigned to you"
    />
</template>
