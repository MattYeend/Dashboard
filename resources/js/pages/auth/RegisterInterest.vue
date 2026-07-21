<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import { store } from '@/routes/register';
import RegisterInterestForm from './components/RegisterInterestForm.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    company: '',
    message: '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        phone: nullIfBlank(data.phone),
        company: nullIfBlank(data.company),
        message: nullIfBlank(data.message),
    })).post(store().url);
};
</script>

<template>
    <div class="mx-auto max-w-md">
        <h1 class="text-xl font-semibold">Register your interest</h1>
        <p class="text-sm text-gray-400">
            Leave your details and we'll be in touch — no account is created.
        </p>
        <RegisterInterestForm :form="form" @submit="submit" />
    </div>
</template>
