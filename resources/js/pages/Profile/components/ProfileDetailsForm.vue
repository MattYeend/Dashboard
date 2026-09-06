<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface ProfileDetailsFormData {
    name: string;
    email: string;
}

interface Props {
    errors: Partial<InertiaFormProps<ProfileDetailsFormData>['errors']>;
}

defineProps<Props>();

const name = defineModel<string>('name', { required: true });
const email = defineModel<string>('email', { required: true });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="name">
                Name <span class="text-destructive">*</span>
            </Label>
            <Input
                id="name"
                v-model="name"
                type="text"
                class="mt-1 block w-full"
                autocomplete="name"
                placeholder="Full name"
            />
            <InputError :message="errors.name" />
        </div>

        <div>
            <Label for="email">
                Email address <span class="text-destructive">*</span>
            </Label>
            <Input
                id="email"
                v-model="email"
                type="email"
                class="mt-1 block w-full"
                autocomplete="username"
                placeholder="Email address"
            />
            <InputError :message="errors.email" />
        </div>
    </div>
</template>
