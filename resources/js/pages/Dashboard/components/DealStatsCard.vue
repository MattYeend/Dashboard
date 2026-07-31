<script setup lang="ts">
import { Handshake } from 'lucide-vue-next';
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    total: number;
    won: number;
    lost: number;
}>();
</script>

<template>
    <StatCard label="Deals created" :value="total" :icon="Handshake" />
    <StatCard label="Deals won" :value="won" :hint="`${lost} lost`" />
</template>
