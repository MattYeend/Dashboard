<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { store as plansStore } from '@/routes/plans';
import PlanForm from './components/PlanForm.vue';
import type { PlanFormData } from './components/PlanForm.vue';

const form = useForm<PlanFormData>({
    name: '',
    slug: null,
    description: null,
    price_per_user_per_month: 0,
    is_active: true,
});

function onFormUpdate(updated: PlanFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        slug: nullIfBlank(data.slug),
        description: nullIfBlank(data.description),
        price_per_user_per_month: Math.round(
            data.price_per_user_per_month * 100,
        ),
    })).post(plansStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Plan
            </h1>
            <PlanForm
                :form="form"
                :errors="form.errors"
                submit-label="Create Plan"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
