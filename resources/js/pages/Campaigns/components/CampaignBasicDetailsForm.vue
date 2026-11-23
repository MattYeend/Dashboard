<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { SelectOption } from '@/types';
import type { CampaignFormData } from '@/types/forms';

const props = defineProps<{
    form: CampaignFormData;
    errors: Partial<Record<keyof CampaignFormData, string>>;
    templateOptions: SelectOption[];
    segmentOptions: SelectOption[];
    ownerOptions: SelectOption[];
}>();

const emit = defineEmits<{
    (e: 'update:form', value: CampaignFormData): void;
}>();

function update<K extends keyof CampaignFormData>(
    field: K,
    value: CampaignFormData[K],
): void {
    emit('update:form', { ...props.form, [field]: value });
}

function onSelect(
    field: 'email_template_id' | 'campaign_segment_id' | 'owner_id',
    event: Event,
): void {
    update(field, (event.target as HTMLSelectElement).value);
}
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <Label for="name"
                >Name <span class="text-destructive">*</span></Label
            >
            <Input
                id="name"
                :model-value="form.name"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter campaign name"
                @update:model-value="update('name', $event as string)"
            />
            <InputError :message="errors.name" />
        </div>

        <div>
            <Label for="email_template_id"
                >Email template <span class="text-destructive">*</span></Label
            >
            <select
                id="email_template_id"
                :value="form.email_template_id"
                class="mt-1 block w-full rounded-md border px-3 py-2 text-sm"
                @change="onSelect('email_template_id', $event)"
            >
                <option value="">Select email template</option>
                <option
                    v-for="option in templateOptions"
                    :key="option.value"
                    :value="String(option.value)"
                >
                    {{ option.label }}
                </option>
            </select>
            <InputError :message="errors.email_template_id" />
        </div>

        <div>
            <Label for="campaign_segment_id"
                >Segment <span class="text-destructive">*</span></Label
            >
            <select
                id="campaign_segment_id"
                :value="form.campaign_segment_id"
                class="mt-1 block w-full rounded-md border px-3 py-2 text-sm"
                @change="onSelect('campaign_segment_id', $event)"
            >
                <option value="">Select segment</option>
                <option
                    v-for="option in segmentOptions"
                    :key="option.value"
                    :value="String(option.value)"
                >
                    {{ option.label }}
                </option>
            </select>
            <InputError :message="errors.campaign_segment_id" />
        </div>

        <div>
            <Label for="owner_id">Owner</Label>
            <select
                id="owner_id"
                :value="form.owner_id"
                class="mt-1 block w-full rounded-md border px-3 py-2 text-sm"
                @change="onSelect('owner_id', $event)"
            >
                <option value="">Select owner</option>
                <option
                    v-for="option in ownerOptions"
                    :key="option.value"
                    :value="String(option.value)"
                >
                    {{ option.label }}
                </option>
            </select>
            <InputError :message="errors.owner_id" />
        </div>

        <div>
            <Label for="subject">Subject override</Label>
            <Input
                id="subject"
                :model-value="form.subject ?? ''"
                type="text"
                class="mt-1 block w-full"
                placeholder="Leave blank to use the template subject"
                @update:model-value="
                    update('subject', ($event as string) || null)
                "
            />
            <InputError :message="errors.subject" />
        </div>
    </div>
</template>
