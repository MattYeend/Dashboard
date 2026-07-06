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

const registrationNumber = defineModel<string | null>('registrationNumber', {
    default: null,
});
const vatNumber = defineModel<string | null>('vatNumber', { default: null });
const employeeCount = defineModel<number | null>('employeeCount', {
    default: null,
});
const foundedYear = defineModel<number | null>('foundedYear', {
    default: null,
});
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="registration_number">Registration Number</Label>
            <Input
                id="registration_number"
                :model-value="registrationNumber ?? ''"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter company registration number"
                @update:model-value="
                    registrationNumber = ($event as string) || null
                "
            />
            <InputError :message="errors.registration_number" />
        </div>

        <div>
            <Label for="vat_number">VAT Number</Label>
            <Input
                id="vat_number"
                :model-value="vatNumber ?? ''"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter VAT number"
                @update:model-value="vatNumber = ($event as string) || null"
            />
            <InputError :message="errors.vat_number" />
        </div>

        <div>
            <Label for="employee_count">Employee Count</Label>
            <Input
                id="employee_count"
                :model-value="employeeCount ?? ''"
                type="number"
                min="0"
                class="mt-1 block w-full"
                placeholder="Enter number of employees"
                @update:model-value="
                    employeeCount = $event === '' ? null : Number($event)
                "
            />
            <InputError :message="errors.employee_count" />
        </div>

        <div>
            <Label for="founded_year">Founded Year</Label>
            <Input
                id="founded_year"
                :model-value="foundedYear ?? ''"
                type="number"
                min="1800"
                :max="new Date().getFullYear()"
                class="mt-1 block w-full"
                placeholder="Enter year founded"
                @update:model-value="
                    foundedYear = $event === '' ? null : Number($event)
                "
            />
            <InputError :message="errors.founded_year" />
        </div>
    </div>
</template>
