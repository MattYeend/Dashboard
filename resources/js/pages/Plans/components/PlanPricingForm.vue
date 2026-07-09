<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import type { PlanFormData } from './PlanForm.vue';

interface Errors {
    price_per_user_per_month?: string;
    is_active?: string;
}

const props = defineProps<{
    form: PlanFormData;
    errors: Errors;
}>();

const emit = defineEmits<{
    (e: 'update:form', value: PlanFormData): void;
}>();

function update<K extends keyof PlanFormData>(
    field: K,
    value: PlanFormData[K],
): void {
    emit('update:form', { ...props.form, [field]: value });
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="price_per_user_per_month"
                >Price per user, per month (£)
                <span class="text-destructive">*</span></Label
            >
            <Input
                id="price_per_user_per_month"
                :model-value="form.price_per_user_per_month"
                type="number"
                step="0.01"
                min="0"
                class="mt-1 block w-full"
                placeholder="0.00"
                @update:model-value="
                    update('price_per_user_per_month', Number($event))
                "
            />
            <InputError :message="errors.price_per_user_per_month" />
        </div>
        <div class="flex items-center space-x-2">
            <Switch
                id="is_active"
                :model-value="form.is_active"
                @update:model-value="update('is_active', $event as boolean)"
            />
            <Label for="is_active">Active</Label>
            <InputError :message="errors.is_active" />
        </div>
    </div>
</template>
