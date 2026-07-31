<script setup lang="ts">
import { ShoppingCart } from 'lucide-vue-next';
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    total: number;
    completed: number;
    outstanding: number;
}>();
</script>

<template>
    <StatCard
        label="Orders"
        :value="total"
        :icon="ShoppingCart"
        :hint="`${completed} delivered, ${outstanding} outstanding`"
    />
</template>
