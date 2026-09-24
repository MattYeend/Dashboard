<script setup lang="ts">
import { dashboard } from '@/routes';
import { edit } from '@/routes/profile';
import type { ApiToken, User, UserSession } from '@/types';
import ProfileApiTokens from './components/ProfileApiTokens.vue';
import ProfileDeleteAccount from './components/ProfileDeleteAccount.vue';
import ProfileForm from './components/ProfileForm.vue';
import ProfilePasswordForm from './components/ProfilePasswordForm.vue';
import ProfileSessions from './components/ProfileSessions.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard().url },
            { title: 'Edit profile', href: edit().url },
        ],
    },
});

defineProps<{
    user: User;
    sessions: UserSession[];
    canManageTokens: boolean;
    tokens: ApiToken[];
    tokenAbilities: string[];
    tokenLifetimes: number[];
    newToken: string | null;
}>();
</script>

<template>
    <div class="mx-auto flex max-w-3xl flex-col gap-6 p-4">
        <h1 class="text-xl font-semibold">Edit profile</h1>

        <ProfileForm :user="user" />
        <ProfilePasswordForm />
        <ProfileSessions :sessions="sessions" />
        <ProfileApiTokens
            v-if="canManageTokens"
            :tokens="tokens"
            :abilities="tokenAbilities"
            :lifetimes="tokenLifetimes"
            :new-token="newToken"
        />
        <ProfileDeleteAccount />
    </div>
</template>
