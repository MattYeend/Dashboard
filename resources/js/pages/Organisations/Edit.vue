<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import OrganisationForm from '@/pages/Organisations/components/OrganisationForm.vue';
import type { Organisation } from '@/types';
import { update as organisationsUpdate } from '@/routes/organisations';

interface Props {
    organisation: Organisation;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.organisation.name,
    slug: props.organisation.slug,
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        slug: nullIfBlank(data.slug),
    })).put(organisationsUpdate.url(props.organisation.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Edit Organisation</h1>

            <OrganisationForm
                v-model:name="form.name"
                v-model:slug="form.slug"
                :is-editing="true"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
