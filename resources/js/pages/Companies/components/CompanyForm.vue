<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import CompanyBasicDetailsForm from '@/pages/Companies/components/CompanyBasicDetailsForm.vue';
import CompanyContactDetailsForm from '@/pages/Companies/components/CompanyContactDetailsForm.vue';
import CompanyDescriptionForm from '@/pages/Companies/components/CompanyDescriptionForm.vue';
import CompanyRegistrationDetailsForm from '@/pages/Companies/components/CompanyRegistrationDetailsForm.vue';
import { index as companiesIndex } from '@/routes/companies';
import type { Industry } from '@/types';

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
    isEditing: boolean;
    processing: boolean;
    industries: Industry[];
    errors: Partial<InertiaFormProps<CompanyFormData>['errors']>;
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const name = defineModel<string>('name', { required: true });
const industryId = defineModel<number | null>('industryId', { default: null });
const email = defineModel<string | null>('email', { default: null });
const phone = defineModel<string | null>('phone', { default: null });
const website = defineModel<string | null>('website', { default: null });
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
const description = defineModel<string | null>('description', {
    default: null,
});
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <CompanyBasicDetailsForm
            v-model:name="name"
            v-model:industry-id="industryId"
            :industries="industries"
            :errors="errors"
        />
        <CompanyContactDetailsForm
            v-model:email="email"
            v-model:phone="phone"
            v-model:website="website"
            :errors="errors"
        />
        <CompanyRegistrationDetailsForm
            v-model:registration-number="registrationNumber"
            v-model:vat-number="vatNumber"
            v-model:employee-count="employeeCount"
            v-model:founded-year="foundedYear"
            :errors="errors"
        />
        <CompanyDescriptionForm
            v-model:description="description"
            :errors="errors"
        />

        <div class="flex items-center justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="companiesIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Company' : 'Create Company' }}
            </Button>
        </div>
    </form>
</template>
