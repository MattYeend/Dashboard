<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { index as campaignsIndex } from '@/routes/campaigns';
import type { SelectOption } from '@/types';
import type { CampaignFormData } from '@/types/forms';
import CampaignBasicDetailsForm from './CampaignBasicDetailsForm.vue';
import CampaignScheduleForm from './CampaignScheduleForm.vue';
import CampaignSenderForm from './CampaignSenderForm.vue';

defineProps<{
    form: CampaignFormData;
    errors: Partial<Record<keyof CampaignFormData, string>>;
    templateOptions: SelectOption[];
    segmentOptions: SelectOption[];
    ownerOptions: SelectOption[];
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: CampaignFormData): void;
}>();
</script>

<template>
    <form class="space-y-8" @submit.prevent="emit('submit')">
        <section class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-300">Basic details</h2>
            <CampaignBasicDetailsForm
                :form="form"
                :errors="errors"
                :template-options="templateOptions"
                :segment-options="segmentOptions"
                :owner-options="ownerOptions"
                @update:form="emit('update:form', $event)"
            />
        </section>

        <section class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-300">Sender</h2>
            <CampaignSenderForm
                :form="form"
                :errors="errors"
                @update:form="emit('update:form', $event)"
            />
        </section>

        <section class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-300">Schedule</h2>
            <CampaignScheduleForm
                :form="form"
                :errors="errors"
                @update:form="emit('update:form', $event)"
            />
        </section>

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="campaignsIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ submitLabel }}
            </Button>
        </div>
    </form>
</template>