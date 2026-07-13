<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { Industry, UserOption } from '@/types';

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
    account_manager_id: number | null;
    employee_count: number | null;
    founded_year: number | null;
}

interface Props {
    industries: Industry[];
    users: UserOption[];
    errors: Partial<InertiaFormProps<CompanyFormData>['errors']>;
}

defineProps<Props>();

const name = defineModel<string>('name', { required: true });
const industryId = defineModel<number | null>('industryId', { default: null });
const accountManagerId = defineModel<number | null>('accountManagerId', {
    default: null,
});
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
                placeholder="Enter company name"
            />
            <InputError :message="errors.name" />
        </div>

        <div>
            <Label for="industry_id">Industry</Label>
            <Select v-model="industryId">
                <SelectTrigger id="industry_id" class="mt-1 w-full">
                    <SelectValue placeholder="Select an industry" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="industry in industries"
                        :key="industry.id"
                        :value="industry.id"
                    >
                        {{ industry.title }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.industry_id" />
        </div>
        <div>
            <Label for="account_manager_id">Account Manager</Label>
            <Select v-model="accountManagerId">
                <SelectTrigger id="account_manager_id" class="mt-1 w-full">
                    <SelectValue placeholder="Select an account manager" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="user in users"
                        :key="user.id"
                        :value="user.id"
                    >
                        {{ user.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.account_manager_id" />
        </div>
    </div>
</template>
