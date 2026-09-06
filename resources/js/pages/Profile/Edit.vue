<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { nullIfBlank } from '@/lib/forms';
import {
    edit as profileEdit,
    update as profileUpdate,
    updatePassword as profileUpdatePassword,
    destroy as profileDestroy,
} from '@/routes/profile';
import type { AuthUser } from '@/types';
import ProfileDetailsForm from './components/ProfileDetailsForm.vue';
import ProfilePasswordForm from './components/ProfilePasswordForm.vue';

interface Props {
    user: AuthUser;
}

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Profile settings', href: profileEdit() }],
    },
});

const detailsForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

function submitDetails(): void {
    detailsForm
        .transform((data) => ({
            name: nullIfBlank(data.name),
            email: nullIfBlank(data.email),
        }))
        .patch(profileUpdate().url, { preserveScroll: true });
}

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submitPassword(): void {
    passwordForm.patch(profileUpdatePassword().url, {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
}

const deleteDialogOpen = ref(false);
const deleteForm = useForm({});

function deleteProfile(): void {
    deleteForm.delete(profileDestroy().url, {
        onFinish: () => {
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl space-y-8 px-4 sm:px-6 lg:px-8">
            <div>
                <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                    Profile
                </h1>

                <form class="space-y-6" @submit.prevent="submitDetails">
                    <ProfileDetailsForm
                        v-model:name="detailsForm.name"
                        v-model:email="detailsForm.email"
                        :errors="detailsForm.errors"
                    />
                    <Button type="submit" :disabled="detailsForm.processing">
                        Save
                    </Button>
                </form>
            </div>

            <div>
                <h2 class="mb-6 text-2xl font-semibold text-gray-300">
                    Update password
                </h2>

                <form class="space-y-6" @submit.prevent="submitPassword">
                    <ProfilePasswordForm
                        v-model:current-password="passwordForm.current_password"
                        v-model:password="passwordForm.password"
                        v-model:password-confirmation="
                            passwordForm.password_confirmation
                        "
                        :errors="passwordForm.errors"
                    />
                    <Button type="submit" :disabled="passwordForm.processing">
                        Update password
                    </Button>
                </form>
            </div>

            <div class="rounded-lg border border-destructive/50 p-4">
                <h2 class="mb-2 text-lg font-semibold text-destructive">
                    Delete account
                </h2>
                <p class="mb-4 text-sm text-muted-foreground">
                    Once your account is deleted, all of its resources and data
                    will be permanently deleted.
                </p>
                <Button
                    type="button"
                    variant="destructive"
                    @click="deleteDialogOpen = true"
                >
                    Delete account
                </Button>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete your account"
            description="This will permanently delete your account. This action cannot be undone."
            confirm-label="Delete account"
            :processing="deleteForm.processing"
            @confirm="deleteProfile"
        />
    </div>
</template>
