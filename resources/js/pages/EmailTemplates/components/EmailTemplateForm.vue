<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import EmailTemplateBasicDetailsForm from '@/pages/EmailTemplates/components/EmailTemplateBasicDetailsForm.vue';
import EmailTemplateContentForm from '@/pages/EmailTemplates/components/EmailTemplateContentForm.vue';
import { index as emailTemplatesIndex } from '@/routes/email-templates';
import type { EmailTemplateFormData } from '@/types/forms';

interface Props {
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<EmailTemplateFormData>['errors']>;
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const name = defineModel<string>('name', { required: true });
const subject = defineModel<string>('subject', { required: true });
const preheader = defineModel<string | null>('preheader', { default: null });
const isActive = defineModel<boolean>('isActive', { required: true });
const bodyHtml = defineModel<string>('bodyHtml', { required: true });
const bodyText = defineModel<string | null>('bodyText', { default: null });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <EmailTemplateBasicDetailsForm
            v-model:name="name"
            v-model:subject="subject"
            v-model:preheader="preheader"
            v-model:is-active="isActive"
            :errors="errors"
        />

        <EmailTemplateContentForm
            v-model:body-html="bodyHtml"
            v-model:body-text="bodyText"
            :errors="errors"
        />

        <div class="flex justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="emailTemplatesIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Email Template' : 'Create Email Template' }}
            </Button>
        </div>
    </form>
</template>