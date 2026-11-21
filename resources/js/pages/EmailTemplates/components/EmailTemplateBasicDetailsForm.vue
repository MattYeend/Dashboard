<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { EmailTemplateFormData } from '@/types/forms';

interface Props {
    errors: Partial<InertiaFormProps<EmailTemplateFormData>['errors']>;
}

defineProps<Props>();

const name = defineModel<string>('name', { required: true });
const subject = defineModel<string>('subject', { required: true });
const preheader = defineModel<string | null>('preheader', { default: null });
const isActive = defineModel<boolean>('isActive', { required: true });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="name"
                >Name <span class="text-destructive">*</span></Label
            >
            <Input
                id="name"
                v-model="name"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter template name"
            />
            <InputError :message="errors.name" />
        </div>

        <div>
            <Label for="subject"
                >Subject <span class="text-destructive">*</span></Label
            >
            <Input
                id="subject"
                v-model="subject"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter email subject"
            />
            <InputError :message="errors.subject" />
        </div>

        <div>
            <Label for="preheader">Preheader</Label>
            <Input
                id="preheader"
                :model-value="preheader ?? ''"
                type="text"
                class="mt-1 block w-full"
                placeholder="Short preview text shown in inboxes"
                @update:model-value="preheader = ($event as string) || null"
            />
            <InputError :message="errors.preheader" />
        </div>

        <div class="flex items-center space-x-2">
            <Checkbox id="is_active" v-model:checked="isActive" />
            <Label for="is_active">Active</Label>
        </div>
        <InputError :message="errors.is_active" />
    </div>
</template>
