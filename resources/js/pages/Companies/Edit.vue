<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import { update as companiesUpdate } from '@/routes/companies';
import type { Company, Industry } from '@/types';
import CompanyForm from './components/CompanyForm.vue';

interface Props {
    company: Company;
    industries: Industry[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.company.name,
    email: props.company.email,
    phone: props.company.phone,
    website: props.company.website,
    registration_number: props.company.registration_number,
    vat_number: props.company.vat_number,
    description: props.company.description,
    industry_id: numberOrNull(props.company.industry_id),
    employee_count: props.company.employee_count,
    founded_year: props.company.founded_year,
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        email: nullIfBlank(data.email),
        phone: nullIfBlank(data.phone),
        website: nullIfBlank(data.website),
        registration_number: nullIfBlank(data.registration_number),
        vat_number: nullIfBlank(data.vat_number),
        description: nullIfBlank(data.description),
        industry_id: numberOrNull(data.industry_id),
        employee_count: numberOrNull(data.employee_count),
        founded_year: numberOrNull(data.founded_year),
    })).put(companiesUpdate.url(props.company.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Company
            </h1>
            <CompanyForm
                v-model:name="form.name"
                v-model:industry-id="form.industry_id"
                v-model:email="form.email"
                v-model:phone="form.phone"
                v-model:website="form.website"
                v-model:registration-number="form.registration_number"
                v-model:vat-number="form.vat_number"
                v-model:employee-count="form.employee_count"
                v-model:founded-year="form.founded_year"
                v-model:description="form.description"
                :is-editing="true"
                :processing="form.processing"
                :industries="industries"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
