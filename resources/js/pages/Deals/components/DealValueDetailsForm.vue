<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface DealValueFormData {
    value: number;
    currency: string;
    probability: number;
    expected_close_date: string | null;
    closed_at: string | null;
}

interface Props {
    errors: Partial<InertiaFormProps<DealValueFormData>['errors']>;
}

defineProps<Props>();

const value = defineModel<number>('value', { required: true });
const currency = defineModel<string>('currency', { required: true });
const probability = defineModel<number>('probability', { required: true });
const expectedCloseDate = defineModel<string | null>('expectedCloseDate', {
    default: null,
});
const closedAt = defineModel<string | null>('closedAt', { default: null });
</script>

<template>
    <div class="space-y-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <Label for="value">Value</Label>
                <Input
                    id="value"
                    v-model.number="value"
                    type="number"
                    min="0"
                    class="mt-1 block w-full"
                    placeholder="0"
                />
                <InputError :message="errors.value" />
            </div>

            <div>
                <Label for="currency">Currency</Label>
                <Input
                    id="currency"
                    v-model="currency"
                    type="text"
                    maxlength="3"
                    class="mt-1 block w-full uppercase"
                    placeholder="GBP"
                />
                <InputError :message="errors.currency" />
            </div>
        </div>

        <div>
            <Label for="probability">Probability (%)</Label>
            <Input
                id="probability"
                v-model.number="probability"
                type="number"
                min="0"
                max="100"
                class="mt-1 block w-full"
                placeholder="0"
            />
            <InputError :message="errors.probability" />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <Label for="expected_close_date">Expected close date</Label>
                <Input
                    id="expected_close_date"
                    :model-value="expectedCloseDate ?? ''"
                    type="date"
                    class="mt-1 block w-full"
                    @update:model-value="
                        expectedCloseDate = ($event as string) || null
                    "
                />
                <InputError :message="errors.expected_close_date" />
            </div>

            <div>
                <Label for="closed_at">Closed date</Label>
                <Input
                    id="closed_at"
                    :model-value="closedAt ?? ''"
                    type="date"
                    class="mt-1 block w-full"
                    @update:model-value="closedAt = ($event as string) || null"
                />
                <InputError :message="errors.closed_at" />
            </div>
        </div>
    </div>
</template>
