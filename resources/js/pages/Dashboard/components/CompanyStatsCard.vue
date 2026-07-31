<script setup lang="ts">
import { Building2 } from 'lucide-vue-next';
import StatCard from '@/pages/Dashboard/components/StatCard.vue';

defineProps<{
    total: number;
    createdThisMonth: number;
}>();
</script>

<template>
    <StatCard
        label="Companies"
        :value="total"
        :icon="Building2"
        :hint="`${createdThisMonth} created this month`"
    />
</template>
