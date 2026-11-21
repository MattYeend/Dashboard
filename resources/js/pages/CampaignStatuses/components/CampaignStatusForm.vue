<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { index as campaignStatusesIndex } from '@/routes/campaign-statuses';
import type { CampaignStatusFormData } from '@/types/forms';
import CampaignStatusBasicDetailsForm from './CampaignStatusBasicDetailsForm.vue';
import CampaignStatusColourForm from './CampaignStatusColourForm.vue';

interface Errors {
    title?: string;
    description?: string;
    background_colour?: string;
    text_colour?: string;
}

defineProps<{
    form: CampaignStatusFormData;
    errors: Errors;
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: CampaignStatusFormData): void;
}>();
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <CampaignStatusBasicDetailsForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', $event)"
        />
        <CampaignStatusColourForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', $event)"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="campaignStatusesIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>
