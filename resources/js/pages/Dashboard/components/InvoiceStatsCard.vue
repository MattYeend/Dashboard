<script setup lang="ts">
import { ReceiptText } from 'lucide-vue-next';
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    total: number;
    paid: number;
    outstanding: number;
}>();
</script>

<template>
    <StatCard
        label="Invoices"
        :value="total"
        :icon="ReceiptText"
        :hint="`${paid} paid, ${outstanding} outstanding`"
    />
</template>
