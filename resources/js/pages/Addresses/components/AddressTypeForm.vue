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

interface AddressTypeFormData {
    addressable_type: string;
    addressable_id: number | null;
}

interface Props {
    errors: Partial<InertiaFormProps<AddressTypeFormData>['errors']>;
    addressableTypes: { value: string; label: string }[];
    addressableOptions: { value: number; label: string }[];
}

defineProps<Props>();

const addressableType = defineModel<string>('addressableType', {
    required: true,
});
const addressableId = defineModel<number | null>('addressableId', {
    required: true,
});
</script>

<template>
    <div>
        <Label for="addressable_type">Address owner type</Label>
        <Select v-model="addressableType">
            <SelectTrigger id="addressable_type" class="mt-1 w-full">
                <SelectValue placeholder="Select type" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="type in addressableTypes"
                    :key="type.value"
                    :value="type.value"
                >
                    {{ type.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.addressable_type" />
    </div>

    <div>
        <Label for="addressable_id">Address owner</Label>
        <Select v-model="addressableId" :disabled="!addressableType">
            <SelectTrigger id="addressable_id" class="mt-1 w-full">
                <SelectValue placeholder="Select owner" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in addressableOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.addressable_id" />
    </div>
</template>
