<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { update as plansUpdate } from '@/routes/plans';
import type { Plan } from '@/types';
import PlanForm from './components/PlanForm.vue';
import type { PlanFormData } from './components/PlanForm.vue';

const props = defineProps<{
    plan: Plan;
}>();

const form = useForm<PlanFormData>({
    name: props.plan.name,
    slug: props.plan.slug,
    description: props.plan.description,
    price_per_user_per_month: props.plan.price_per_user_per_month / 100,
    is_active: props.plan.is_active,
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
    })).put(plansUpdate.url(props.plan.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">Edit Plan</h1>
            <PlanForm
                :form="form"
                :errors="form.errors"
                submit-label="Update Plan"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
