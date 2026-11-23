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
            <Label for="from_name">From name</Label>
            <Input
                id="from_name"
                :model-value="form.from_name ?? ''"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter sender name"
                @update:model-value="
                    update('from_name', ($event as string) || null)
                "
            />
            <InputError :message="errors.from_name" />
        </div>

        <div>
            <Label for="reply_to">Reply-to address</Label>
            <Input
                id="reply_to"
                :model-value="form.reply_to ?? ''"
                type="email"
                class="mt-1 block w-full"
                placeholder="Enter reply-to email address"
                @update:model-value="
                    update('reply_to', ($event as string) || null)
                "
            />
            <InputError :message="errors.reply_to" />
        </div>
    </div>
</template>