<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { index as emailSuppressionsIndex } from '@/routes/email-suppressions';
import type { SelectOption } from '@/types';
import type { EmailSuppressionFormData } from '@/types/forms';
import EmailSuppressionBasicDetailsForm from './EmailSuppressionBasicDetailsForm.vue';

defineProps<{
    form: EmailSuppressionFormData;
    errors: Partial<Record<keyof EmailSuppressionFormData, string>>;
    reasonOptions: SelectOption[];
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: EmailSuppressionFormData): void;
}>();
</script>

<template>
    <form class="space-y-8" @submit.prevent="emit('submit')">
        <section class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-300">Basic details</h2>
            <EmailSuppressionBasicDetailsForm
                :form="form"
                :errors="errors"
                :reason-options="reasonOptions"
                @update:form="emit('update:form', $event)"
            />
        </section>

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="emailSuppressionsIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
