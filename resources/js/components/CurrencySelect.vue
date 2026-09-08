<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CURRENCY_OPTIONS } from '@/lib/currencies';

const model = defineModel<string>({ required: true });
</script>

<template>
    <Select v-model="model">
        <SelectTrigger>
            <SelectValue placeholder="Select a currency" />
        </SelectTrigger>
        <SelectContent>
            <SelectItem v-for="option in CURRENCY_OPTIONS" :key="option.value" :value="option.value">
                {{ option.label }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>