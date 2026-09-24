<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { update } from '@/routes/profile';
import type { User } from '@/types';
import ProfileBasicDetailsForm from './ProfileBasicDetailsForm.vue';

interface ProfileFormData {
    name: string;
    email: string;
}

const props = defineProps<{
    user: User;
}>();

const form = useForm<ProfileFormData>({
    name: props.user.name,
    email: props.user.email,
});

function submit(): void {
    form.put(update().url, { preserveScroll: true });
}
</script>

<template>
    <form class="flex flex-col gap-4 rounded-lg border border-gray-500 p-4" @submit.prevent="submit">
        <h2 class="text-sm font-medium text-gray-300">Basic details</h2>

        <ProfileBasicDetailsForm
            v-model:name="form.name"
            v-model:email="form.email"
            :errors="form.errors"
        />

        <p v-if="user.email_verified_at === null" class="text-xs text-gray-400">
            Your email address is not verified.
        </p>

        <div>
            <Button type="submit" :disabled="form.processing">Save</Button>
        </div>
    </form>
</template>