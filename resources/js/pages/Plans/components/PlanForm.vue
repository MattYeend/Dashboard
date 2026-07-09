<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { index as plansIndex } from '@/routes/plans';
import PlanBasicDetailsForm from './PlanBasicDetailsForm.vue';
import PlanDescriptionForm from './PlanDescriptionForm.vue';
import PlanPricingForm from './PlanPricingForm.vue';

export interface PlanFormData {
    name: string;
    slug: string | null;
    description: string | null;
    price_per_user_per_month: number;
    is_active: boolean;
}

interface Errors {
    name?: string;
    slug?: string;
    description?: string;
    price_per_user_per_month?: string;
    is_active?: string;
}

defineProps<{
    form: PlanFormData;
    errors: Errors;
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: PlanFormData): void;
}>();
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <PlanBasicDetailsForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />
        <PlanPricingForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />
        <PlanDescriptionForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="plansIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
