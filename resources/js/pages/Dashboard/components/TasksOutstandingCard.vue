<script setup lang="ts">
import { ListTodo } from 'lucide-vue-next';
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    outstanding: number;
}>();
</script>

<template>
    <StatCard
        label="Tasks outstanding"
        :value="outstanding"
        :icon="ListTodo"
        hint="Assigned to you"
    />
</template>
