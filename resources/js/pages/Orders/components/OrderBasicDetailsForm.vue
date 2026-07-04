<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { OrderStatus } from '@/types';

interface OrderBasicFormData {
    title: string;
    description: string | null;
    notes: string | null;
    subtotal: number;
    discount_amount: number;
    tax_amount: number;
    total_amount: number;
    ordered_at: string | null;
    due_at: string | null;
    completed_at: string | null;
    status_id: number | null;
}

interface Props {
    statuses: OrderStatus[];
    errors: Partial<InertiaFormProps<OrderBasicFormData>['errors']>;
}

defineProps<Props>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    default: null,
});
const notes = defineModel<string | null>('notes', { default: null });
const subtotal = defineModel<number>('subtotal', { required: true });
const discountAmount = defineModel<number>('discountAmount', {
    required: true,
});
const taxAmount = defineModel<number>('taxAmount', { required: true });
const totalAmount = defineModel<number>('totalAmount', { required: true });
const orderedAt = defineModel<string | null>('orderedAt', { default: null });
const dueAt = defineModel<string | null>('dueAt', { default: null });
const completedAt = defineModel<string | null>('completedAt', {
    default: null,
});
const statusId = defineModel<number | null>('statusId', { default: null });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="title"
                >Title <span class="text-destructive">*</span></Label
            >
            <Input
                id="title"
                v-model="title"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter order title"
            />
            <InputError :message="errors.title" />
        </div>

        <div>
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                :model-value="description ?? ''"
                class="mt-1 block w-full"
                rows="3"
                placeholder="Enter order description"
                @update:model-value="description = ($event as string) || null"
            />
            <InputError :message="errors.description" />
        </div>

        <div>
            <Label for="notes">Notes</Label>
            <Textarea
                id="notes"
                :model-value="notes ?? ''"
                class="mt-1 block w-full"
                rows="3"
                placeholder="Enter internal notes"
                @update:model-value="notes = ($event as string) || null"
            />
            <InputError :message="errors.notes" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <Label for="subtotal">Subtotal</Label>
                <Input
                    id="subtotal"
                    v-model.number="subtotal"
                    type="number"
                    step="0.01"
                    min="0"
                    class="mt-1 block w-full"
                />
                <InputError :message="errors.subtotal" />
            </div>
            <div>
                <Label for="discount_amount">Discount</Label>
                <Input
                    id="discount_amount"
                    v-model.number="discountAmount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="mt-1 block w-full"
                />
                <InputError :message="errors.discount_amount" />
            </div>
            <div>
                <Label for="tax_amount">Tax</Label>
                <Input
                    id="tax_amount"
                    v-model.number="taxAmount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="mt-1 block w-full"
                />
                <InputError :message="errors.tax_amount" />
            </div>
            <div>
                <Label for="total_amount">Total</Label>
                <Input
                    id="total_amount"
                    v-model.number="totalAmount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="mt-1 block w-full"
                />
                <InputError :message="errors.total_amount" />
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <Label for="ordered_at">Ordered At</Label>
                <Input
                    id="ordered_at"
                    :model-value="orderedAt ?? ''"
                    type="datetime-local"
                    class="mt-1 block w-full"
                    @update:model-value="orderedAt = ($event as string) || null"
                />
                <InputError :message="errors.ordered_at" />
            </div>
            <div>
                <Label for="due_at">Due At</Label>
                <Input
                    id="due_at"
                    :model-value="dueAt ?? ''"
                    type="datetime-local"
                    class="mt-1 block w-full"
                    @update:model-value="dueAt = ($event as string) || null"
                />
                <InputError :message="errors.due_at" />
            </div>
            <div>
                <Label for="completed_at">Completed At</Label>
                <Input
                    id="completed_at"
                    :model-value="completedAt ?? ''"
                    type="datetime-local"
                    class="mt-1 block w-full"
                    @update:model-value="
                        completedAt = ($event as string) || null
                    "
                />
                <InputError :message="errors.completed_at" />
            </div>
        </div>

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
    </div>
</template>
