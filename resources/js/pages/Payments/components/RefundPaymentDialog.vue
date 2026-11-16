<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { refund } from '@/routes/payments';
import type { Payment } from '@/types';

import type { RefundFormData } from '@/types/forms';

const props = defineProps<{
    payment: Payment;
}>();

const open = defineModel<boolean>('open', { required: true });

const form = useForm<RefundFormData>({
    amount: props.payment.refundable_amount ?? props.payment.amount,
    method: props.payment.method,
    paid_at: new Date().toISOString().slice(0, 16),
    reference: null,
    notes: '',
});

/**
 * Submit the refund once. The button is disabled while the request is in
 * flight.
 */
function submit(): void {
    form.transform((data) => ({
        ...data,
        reference: data.reference?.trim() || null,
    })).post(refund.url(props.payment.id), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
            form.reset();
        },
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Refund payment</DialogTitle>
                <DialogDescription>
                    A refund is recorded as a separate entry and cannot be
                    undone.
                </DialogDescription>
            </DialogHeader>

            <form class="grid gap-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="refund_amount">Amount (GBP)</Label>
                    <Input
                        id="refund_amount"
                        v-model="form.amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        :max="payment.refundable_amount ?? payment.amount"
                    />
                    <InputError :message="form.errors.amount" />
                </div>

                <div class="grid gap-2">
                    <Label for="refund_notes">Reason</Label>
                    <Textarea
                        id="refund_notes"
                        v-model="form.notes"
                        rows="3"
                        maxlength="2000"
                    />
                    <InputError :message="form.errors.notes" />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="open = false"
                        >Cancel</Button
                    >
                    <Button type="submit" :disabled="form.processing"
                        >Refund</Button
                    >
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
