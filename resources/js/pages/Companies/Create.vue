<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank, numberOrNull } from '@/lib/forms';
import { store as companiesStore } from '@/routes/companies';
import type { Industry, UserOption } from '@/types';
import CompanyForm from './components/CompanyForm.vue';

defineProps<{
    industries: Industry[];
    users: UserOption[];
}>();

const form = useForm({
    name: '',
    email: null,
    phone: null,
    website: null,
    registration_number: null,
    vat_number: null,
    description: null,
    industry_id: null,
    account_manager_id: null,
    employee_count: null,
    founded_year: null,
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
        account_manager_id: numberOrNull(data.account_manager_id),
        employee_count: numberOrNull(data.employee_count),
        founded_year: numberOrNull(data.founded_year),
    })).post(companiesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Company
            </h1>
            <CompanyForm
                v-model:name="form.name"
                v-model:industry-id="form.industry_id"
                v-model:account-manager-id="form.account_manager_id"
                v-model:email="form.email"
                v-model:phone="form.phone"
                v-model:website="form.website"
                v-model:registration-number="form.registration_number"
                v-model:vat-number="form.vat_number"
                v-model:employee-count="form.employee_count"
                v-model:founded-year="form.founded_year"
                v-model:description="form.description"
                :is-editing="false"
                :processing="form.processing"
                :industries="industries"
                :users="users"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
