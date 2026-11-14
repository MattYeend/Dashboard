<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { InvoiceFormData } from '@/types/forms';

interface Props {
    errors: Partial<InertiaFormProps<InvoiceFormData>['errors']>;
}

defineProps<Props>();

const issueDate = defineModel<string | null>('issueDate', { default: null });
const dueDate = defineModel<string | null>('dueDate', { default: null });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="issue_date">Issue Date</Label>
            <Input
                id="issue_date"
                :model-value="issueDate ?? ''"
                type="date"
                class="mt-1 block w-full"
                @update:model-value="issueDate = ($event as string) || null"
            />
            <InputError :message="errors.issue_date" />
        </div>

        <div>
            <Label for="due_date">Due Date</Label>
            <Input
                id="due_date"
                :model-value="dueDate ?? ''"
                type="date"
                class="mt-1 block w-full"
                @update:model-value="dueDate = ($event as string) || null"
            />
            <InputError :message="errors.due_date" />
        </div>
    </div>
</template>
