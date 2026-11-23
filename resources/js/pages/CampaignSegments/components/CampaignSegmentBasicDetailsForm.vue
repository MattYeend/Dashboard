<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { CampaignSegmentFormData } from '@/types/forms';
 
interface Errors {
    name?: string;
    description?: string;
}
 
const props = defineProps<{
    form: CampaignSegmentFormData;
    errors: Errors;
}>();
 
const emit = defineEmits<{
    (e: 'update:form', value: CampaignSegmentFormData): void;
}>();
 
function update<K extends keyof CampaignSegmentFormData>(
    field: K,
    value: CampaignSegmentFormData[K],
): void {
    emit('update:form', { ...props.form, [field]: value });
}
</script>
 
<template>
    <div class="space-y-4">
        <div>
            <Label for="name"
                >Name <span class="text-destructive">*</span></Label
            >
            <Input
                id="name"
                :model-value="form.name"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter segment name"
                @update:model-value="update('name', $event as string)"
            />
            <InputError :message="errors.name" />
        </div>
        <div>
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                :model-value="form.description ?? ''"
                rows="3"
                class="mt-1 block w-full"
                placeholder="Enter segment description"
                @update:model-value="
                    update('description', ($event as string) || null)
                "
            />
            <InputError :message="errors.description" />
        </div>
    </div>
</template>