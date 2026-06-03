<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import UserBasicDetailsForm from '@/pages/Users/components/UserBasicDetailsForm.vue';
import UserRoleDetailsForm from '@/pages/Users/components/UserRoleDetailsForm.vue';

interface UserFormData {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    role: string;
}

interface Props {
    form: InertiaForm<UserFormData>;
    isEditing: boolean;
}

defineProps<Props>();
defineEmits<{ submit: [] }>();
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <UserBasicDetailsForm :form="form" :is-editing="isEditing" />
        <UserRoleDetailsForm :form="form" />

        <div class="flex items-centre justify-end space-x-3">
            <a :href="route('users.index')" class="rounded-md px-4 py-2 text-sm font-medium text-grey-700">
                Cancel
            </a>
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-centre rounded-md px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
            >
                {{ isEditing ? 'Update User' : 'Create User' }}
            </button>
        </div>
    </form>
</template>