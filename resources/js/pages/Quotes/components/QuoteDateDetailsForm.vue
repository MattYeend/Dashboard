<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import type { QuoteFormData } from '@/types/forms/quotes-form-data';

defineProps<{
    errors: Partial<Record<keyof QuoteFormData, string>>;
}>();

const form = defineModel<QuoteFormData>({ required: true });
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label for="issued_at">Issue date</Label>
            <Input
                id="issued_at"
                v-model="form.issued_at as string"
                type="date"
            />
            <p v-if="errors.issued_at" class="text-sm text-destructive">
                {{ errors.issued_at }}
            </p>
        </div>

        <div class="grid gap-2">
            <Label for="valid_until">Valid until</Label>
            <Input
                id="valid_until"
                v-model="form.valid_until as string"
                type="date"
            />
            <p v-if="errors.valid_until" class="text-sm text-destructive">
                {{ errors.valid_until }}
            </p>
        </div>
    </div>
</template>
