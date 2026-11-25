<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { SelectOption } from '@/types';
import type { EmailSuppressionFormData } from '@/types/forms';

const props = defineProps<{
    form: EmailSuppressionFormData;
    errors: Partial<Record<keyof EmailSuppressionFormData, string>>;
    reasonOptions: SelectOption[];
}>();

const emit = defineEmits<{
    (e: 'update:form', value: EmailSuppressionFormData): void;
}>();

function update<K extends keyof EmailSuppressionFormData>(
    field: K,
    value: EmailSuppressionFormData[K],
): void {
    emit('update:form', { ...props.form, [field]: value });
}
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <Label for="email"
                >Email <span class="text-destructive">*</span></Label
            >
            <Input
                id="email"
                :model-value="form.email"
                type="email"
                class="mt-1 block w-full"
                placeholder="Enter email address"
                @update:model-value="update('email', $event as string)"
            />
            <InputError :message="errors.email" />
        </div>

        <div>
            <Label for="reason"
                >Reason <span class="text-destructive">*</span></Label
            >
            <select
                id="reason"
                :value="form.reason"
                class="mt-1 block w-full rounded-md border px-3 py-2 text-sm"
                @change="
                    update('reason', ($event.target as HTMLSelectElement).value)
                "
            >
                <option value="">Select reason</option>
                <option
                    v-for="option in reasonOptions"
                    :key="option.value"
                    :value="String(option.value)"
                >
                    {{ option.label }}
                </option>
            </select>
            <InputError :message="errors.reason" />
        </div>

        <div class="sm:col-span-2">
            <Label for="notes">Notes</Label>
            <Textarea
                id="notes"
                :model-value="form.notes ?? ''"
                rows="3"
                class="mt-1 block w-full"
                placeholder="Why is this address suppressed?"
                @update:model-value="
                    update('notes', ($event as string) || null)
                "
            />
            <InputError :message="errors.notes" />
        </div>
    </div>
</template>
