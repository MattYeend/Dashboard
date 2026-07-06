<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface CompanyFormData {
    name: string;
    slug: string | null;
    email: string | null;
    phone: string | null;
    website: string | null;
    registration_number: string | null;
    vat_number: string | null;
    description: string | null;
    industry_id: number | null;
    employee_count: number | null;
    founded_year: number | null;
}

interface Props {
    errors: Partial<InertiaFormProps<CompanyFormData>['errors']>;
}

defineProps<Props>();

const description = defineModel<string | null>('description', {
    default: null,
});
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                :model-value="description ?? ''"
                rows="4"
                class="mt-1 block w-full"
                placeholder="Enter company description"
                @update:model-value="description = ($event as string) || null"
            />
            <InputError :message="errors.description" />
        </div>
    </div>
</template>
