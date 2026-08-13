<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface TicketBasicFormData {
    title: string;
    description: string | null;
}

interface Props {
    errors: Partial<InertiaFormProps<TicketBasicFormData>['errors']>;
}

defineProps<Props>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    default: null,
});
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="title"
                >Title <span class="text-destructive">*</span></Label
            >
            <Input
                id="title"
                v-model="title"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter ticket title"
            />
            <InputError :message="errors.title" />
        </div>

        <div>
            <Label for="description"
                >Description <span class="text-destructive">*</span></Label
            >
            <Textarea
                id="description"
                :model-value="description ?? ''"
                class="mt-1 block w-full"
                rows="5"
                placeholder="Describe the issue"
                @update:model-value="description = ($event as string) || null"
            />
            <InputError :message="errors.description" />
        </div>
    </div>
</template>
