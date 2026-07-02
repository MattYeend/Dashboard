<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { index as orderStatusesIndex } from '@/routes/order-statuses';
import OrderStatusBasicDetailsForm from './OrderStatusBasicDetailsForm.vue';
import OrderStatusColourForm from './OrderStatusColourForm.vue';

export interface OrderStatusFormData {
    title: string;
    description: string | null;
    background_colour: string;
    text_colour: string;
}

interface Errors {
    title?: string;
    description?: string;
    background_colour?: string;
    text_colour?: string;
}

defineProps<{
    form: OrderStatusFormData;
    errors: Errors;
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: OrderStatusFormData): void;
}>();
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <OrderStatusBasicDetailsForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />
        <OrderStatusColourForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="orderStatusesIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>