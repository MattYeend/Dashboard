<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface OrderTypeFormData {
    orderable_type: string;
    orderable_id: number | null;
}

interface Props {
    errors: Partial<InertiaFormProps<OrderTypeFormData>['errors']>;
    orderableTypes: { value: string; label: string }[];
    orderableOptions: { value: number; label: string }[];
}

defineProps<Props>();

const orderableType = defineModel<string>('orderableType', {
    required: true,
});
const orderableId = defineModel<number | null>('orderableId', {
    required: true,
});
</script>

<template>
    <div>
        <Label for="orderable_type">Order type</Label>
        <Select v-model="orderableType">
            <SelectTrigger id="orderable_type" class="mt-1 w-full">
                <SelectValue placeholder="Select type" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="type in orderableTypes"
                    :key="type.value"
                    :value="type.value"
                >
                    {{ type.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.orderable_type" />
    </div>

    <div>
        <Label for="orderable_id">Order owner</Label>
        <Select v-model="orderableId" :disabled="!orderableType">
            <SelectTrigger id="orderable_id" class="mt-1 w-full">
                <SelectValue placeholder="Select owner" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in orderableOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.orderable_id" />
    </div>
</template>
