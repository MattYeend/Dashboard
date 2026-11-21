<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nullIfBlank } from '@/lib/forms';
import EmailTemplateForm from '@/pages/EmailTemplates/components/EmailTemplateForm.vue';
import { update as emailTemplatesUpdate } from '@/routes/email-templates';
import type { EmailTemplate } from '@/types';
import type { EmailTemplateFormData } from '@/types/forms';

interface Props {
    emailTemplate: EmailTemplate;
}

const props = defineProps<Props>();

const form = useForm<EmailTemplateFormData>({
    name: props.emailTemplate.name,
    subject: props.emailTemplate.subject,
    preheader: props.emailTemplate.preheader ?? null,
    is_active: props.emailTemplate.is_active,
    body_html: props.emailTemplate.body_html,
    body_text: props.emailTemplate.body_text ?? null,
});

function submit(): void {
    form.transform((data) => ({
        ...data,
        preheader: nullIfBlank(data.preheader),
        body_text: nullIfBlank(data.body_text),
    })).put(emailTemplatesUpdate.url(props.emailTemplate.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Edit Email Template
            </h1>

            <EmailTemplateForm
                v-model:name="form.name"
                v-model:subject="form.subject"
                v-model:preheader="form.preheader"
                v-model:is-active="form.is_active"
                v-model:body-html="form.body_html"
                v-model:body-text="form.body_text"
                :is-editing="true"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>