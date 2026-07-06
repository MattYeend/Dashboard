<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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

const email = defineModel<string | null>('email', { default: null });
const phone = defineModel<string | null>('phone', { default: null });
const website = defineModel<string | null>('website', { default: null });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="email">Email</Label>
            <Input
                id="email"
                :model-value="email ?? ''"
                type="email"
                class="mt-1 block w-full"
                placeholder="Enter company email"
                @update:model-value="email = ($event as string) || null"
            />
            <InputError :message="errors.email" />
        </div>

        <div>
            <Label for="phone">Phone</Label>
            <Input
                id="phone"
                :model-value="phone ?? ''"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter company phone number"
                @update:model-value="phone = ($event as string) || null"
            />
            <InputError :message="errors.phone" />
        </div>

        <div>
            <Label for="website">Website</Label>
            <Input
                id="website"
                :model-value="website ?? ''"
                type="url"
                class="mt-1 block w-full"
                placeholder="https://example.com"
                @update:model-value="website = ($event as string) || null"
            />
            <InputError :message="errors.website" />
        </div>
    </div>
</template>
