<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

import type { PaymentFormData } from '@/types/forms';

defineProps<{
    errors: Partial<Record<keyof PaymentFormData, string>>;
}>();

const form = defineModel<PaymentFormData>({ required: true });
</script>

<template>
    <div class="grid gap-4">
        <div class="grid gap-2">
            <Label for="reference">Reference</Label>
            <Input id="reference" v-model="form.reference as string" maxlength="128" />
            <InputError :message="errors.reference" />
        </div>

        <div class="grid gap-2">
            <Label for="notes">Notes</Label>
            <Textarea id="notes" v-model="form.notes as string" rows="3" maxlength="2000" />
            <InputError :message="errors.notes" />
        </div>
    </div>
</template>