<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { CampaignFormData } from '@/types/forms';

const props = defineProps<{
    form: CampaignFormData;
    errors: Partial<Record<keyof CampaignFormData, string>>;
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
</script>

<template>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <Label for="scheduled_at">Scheduled for</Label>
            <Input
                id="scheduled_at"
                :model-value="form.scheduled_at ?? ''"
                type="datetime-local"
                class="mt-1 block w-full"
                @update:model-value="
                    update('scheduled_at', ($event as string) || null)
                "
            />
            <InputError :message="errors.scheduled_at" />
        </div>
    </div>
</template>
