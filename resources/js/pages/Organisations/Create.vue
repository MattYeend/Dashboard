<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import OrganisationForm from '@/pages/Organisations/components/OrganisationForm.vue';
import { store as organisationsStore } from '@/routes/organisations';

const form = useForm({
    name: '',
    slug: '',
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        slug: nullIfBlank(data.slug),
    })).post(organisationsStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Create Organisation</h1>

            <OrganisationForm
                v-model:name="form.name"
                v-model:slug="form.slug"
                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
