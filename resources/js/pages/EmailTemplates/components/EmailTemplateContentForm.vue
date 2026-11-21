<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { EmailTemplateFormData } from '@/types/forms';

interface Props {
    errors: Partial<InertiaFormProps<EmailTemplateFormData>['errors']>;
}

defineProps<Props>();

const bodyHtml = defineModel<string>('bodyHtml', { required: true });
const bodyText = defineModel<string | null>('bodyText', { default: null });

const mergeTagNames = [
    'first_name',
    'name',
    'email',
    'company',
    'unsubscribe_url',
] as const;

const mergeTags = mergeTagNames.map((tag) => `{{ ${tag} }}`);
const unsubscribeTag = '{{ unsubscribe_url }}';
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="body_html"
                >HTML body <span class="text-destructive">*</span></Label
            >
            <Textarea
                id="body_html"
                v-model="bodyHtml"
                class="mt-1 block w-full font-mono"
                rows="12"
                placeholder="Enter HTML body"
            />
            <p class="mt-1 text-xs text-gray-400">
                Allowed merge tags: {{ mergeTags.join(', ') }}.
                {{ unsubscribeTag }} is required.
            </p>
            <InputError :message="errors.body_html" />
        </div>

        <div>
            <Label for="body_text">Plain text body</Label>
            <Textarea
                id="body_text"
                :model-value="bodyText ?? ''"
                class="mt-1 block w-full"
                rows="6"
                placeholder="Enter plain text body"
                @update:model-value="bodyText = ($event as string) || null"
            />
            <InputError :message="errors.body_text" />
        </div>
    </div>
</template>