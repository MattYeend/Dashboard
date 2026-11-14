<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import type { PaymentStatusFormData } from '@/types/forms/payment-statuses-form-data';

defineProps<{
    errors: Partial<Record<keyof PaymentStatusFormData, string>>;
}>();

const form = defineModel<PaymentStatusFormData>({ required: true });
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label for="background_colour">Background colour</Label>
            <Input
                id="background_colour"
                v-model="form.background_colour"
                type="color"
                class="h-10 w-24 p-1"
            />
            <p v-if="errors.background_colour" class="text-sm text-destructive">
                {{ errors.background_colour }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="text_colour">Text colour</Label>
            <Input
                id="text_colour"
                v-model="form.text_colour"
                type="color"
                class="h-10 w-24 p-1"
            />
            <p v-if="errors.text_colour" class="text-sm text-destructive">
                {{ errors.text_colour }}
            </p>
        </div>

        <div class="sm:col-span-2">
            <span
                class="inline-block rounded-md border-2 px-3 py-1 text-sm font-medium"
                :style="{
                    color: form.text_colour,
                    borderColor: form.background_colour,
                }"
            >
                {{ form.title || 'Preview' }}
            </span>
        </div>
    </div>
</template>
