<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { update as orderStatusesUpdate } from '@/routes/order-statuses';
import type { DealStatus } from '@/types';
import type { DealStatusFormData } from './components/DealStatusForm.vue';
import DealStatusForm from './components/DealStatusForm.vue';

const props = defineProps<{
    dealStatus: DealStatus;
}>();

const form = useForm<DealStatusFormData>({
    title: props.dealStatus.title,
    description: props.dealStatus.description,
    background_colour: props.dealStatus.background_colour,
    text_colour: props.dealStatus.text_colour,
});

function onFormUpdate(updated: DealStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        description: nullIfBlank(data.description),
    })).put(orderStatusesUpdate.url(props.dealStatus.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Deal Status
            </h1>
            <DealStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Update Deal Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
