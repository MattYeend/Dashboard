<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { OrderStatus } from '@/types';

defineProps<{
    statuses: OrderStatus[];
    errors: Partial<Record<string, string>>;
}>();

const statusId = defineModel<number | null>('statusId', { default: null });
</script>

<template>
    <div>
        <Label for="status_id">Status</Label>
        <Select v-model="statusId">
            <SelectTrigger id="status_id" class="mt-1 w-full">
                <SelectValue placeholder="Select a status" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="status in statuses"
                    :key="status.id"
                    :value="status.id"
                >
                    {{ status.title }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.status_id" />
    </div>
</template>
