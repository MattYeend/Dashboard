<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import UserBasicDetailsForm from '@/pages/Users/components/UserBasicDetailsForm.vue';
import UserRoleDetailsForm from '@/pages/Users/components/UserRoleDetailsForm.vue';
import { index as usersIndex } from '@/routes/users';

interface UserFormData {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    role: string;
}

interface Props {
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<UserFormData>['errors']>;
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const name = defineModel<string>('name', { required: true });
const email = defineModel<string>('email', { required: true });
const password = defineModel<string>('password', { required: true });
const passwordConfirmation = defineModel<string>('passwordConfirmation', {
    required: true,
});
const role = defineModel<string>('role', { required: true });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <UserBasicDetailsForm
            v-model:name="name"
            v-model:email="email"
            v-model:password="password"
            v-model:password-confirmation="passwordConfirmation"
            :is-editing="isEditing"
            :errors="errors"
        />
        <UserRoleDetailsForm v-model:role="role" :errors="errors" />

        <div class="flex items-center justify-end space-x-3">
            <Link :href="usersIndex.url()" class="text-sm font-medium">
                Cancel
            </Link>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update User' : 'Create User' }}
            </Button>
        </div>
    </form>
</template>
