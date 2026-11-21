<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import EmailTemplateForm from '@/pages/EmailTemplates/components/EmailTemplateForm.vue';
import { store as emailTemplatesStore } from '@/routes/email-templates';
import type { EmailTemplateFormData } from '@/types/forms';

const form = useForm<EmailTemplateFormData>({
    name: '',
    subject: '',
    preheader: null,
    is_active: true,
    body_html: '',
    body_text: null,
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        preheader: nullIfBlank(data.preheader),
        body_text: nullIfBlank(data.body_text),
    })).post(emailTemplatesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Email Template
            </h1>

            <EmailTemplateForm
                v-model:name="form.name"
                v-model:subject="form.subject"
                v-model:preheader="form.preheader"
                v-model:is-active="form.is_active"
                v-model:body-html="form.body_html"
                v-model:body-text="form.body_text"
                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
