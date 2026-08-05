<script setup lang="ts">
import { Handshake } from 'lucide-vue-next';
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    total: number;
}>();
</script>

<template>
    <StatCard label="Deals created" :value="total" :icon="Handshake" />
</template>