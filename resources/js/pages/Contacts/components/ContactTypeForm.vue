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

interface ContactTypeFormData {
    contactable_type: string;
    contactable_id: number | null;
}

interface Props {
    errors: Partial<InertiaFormProps<ContactTypeFormData>['errors']>;
    contactableTypes: { value: string; label: string }[];
    contactableOptions: { value: number; label: string }[];
}

defineProps<Props>();

const contactableType = defineModel<string>('contactableType', {
    required: true,
});
const contactableId = defineModel<number | null>('contactableId', {
    required: true,
});
</script>

<template>
    <div>
        <Label for="contactable_type">Contact type</Label>
        <Select v-model="contactableType">
            <SelectTrigger id="contactable_type" class="mt-1 w-full">
                <SelectValue placeholder="Select type" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="type in contactableTypes"
                    :key="type.value"
                    :value="type.value"
                >
                    {{ type.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.contactable_type" />
    </div>

    <div>
        <Label for="contactable_id">Contact owner</Label>
        <Select v-model="contactableId" :disabled="!contactableType">
            <SelectTrigger id="contactable_id" class="mt-1 w-full">
                <SelectValue placeholder="Select owner" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="option in contactableOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.contactable_id" />
    </div>
</template>
