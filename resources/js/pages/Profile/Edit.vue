<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { nullIfBlank } from '@/lib/forms';
import { update, updatePassword, destroy } from '@/routes/profile';
import type { AuthUser } from '@/types';
import ProfileDetailsForm from './components/ProfileDetailsForm.vue';
import ProfilePasswordForm from './components/ProfilePasswordForm.vue';

const props = defineProps<{
    user: AuthUser;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Profile', href: '#' },
            { title: 'Edit', href: '#' },
        ],
    },
});

const detailsForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

const submitDetails = () => {
    detailsForm
        .transform((data) => ({
            name: nullIfBlank(data.name),
            email: nullIfBlank(data.email),
        }))
        .patch(update().url);
};

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.patch(updatePassword().url, {
        onSuccess: () => passwordForm.reset(),
    });
};

const showDeleteDialog = ref(false);

const deleteProfile = () => {
    useForm({}).delete(destroy().url);
};
</script>

<template>
    <div class="space-y-8">
        <form @submit.prevent="submitDetails">
            <ProfileDetailsForm
                v-model:name="detailsForm.name"
                v-model:email="detailsForm.email"
                :errors="detailsForm.errors"
            />
            <button type="submit" :disabled="detailsForm.processing">
                Save
            </button>
        </form>

        <form @submit.prevent="submitPassword">
            <ProfilePasswordForm
                v-model:current-password="passwordForm.current_password"
                v-model:password="passwordForm.password"
                v-model:password-confirmation="
                    passwordForm.password_confirmation
                "
                :errors="passwordForm.errors"
            />
            <button type="submit" :disabled="passwordForm.processing">
                Update password
            </button>
        </form>

        <div>
            <button type="button" @click="showDeleteDialog = true">
                Delete account
            </button>
            <ConfirmDialog
                v-model:open="showDeleteDialog"
                title="Delete your account"
                description="This will permanently delete your account. This action cannot be undone."
                @confirm="deleteProfile"
            />
        </div>
    </div>
</template>
