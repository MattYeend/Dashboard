<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { edit, show } from '@/routes/profile';
import type { User } from '@/types';
import ProfileBasicDetails from './components/ProfileBasicDetails.vue';
import ProfileDateDetails from './components/ProfileDateDetails.vue';
import ProfileSecurityDetails from './components/ProfileSecurityDetails.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard().url },
            { title: 'Profile', href: show().url },
        ],
    },
});

defineProps<{
    user: User & { two_factor_enabled: boolean };
}>();
</script>

<template>
    <div class="mx-auto flex max-w-3xl flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">My profile</h1>
            <Button as-child>
                <Link :href="edit().url">Edit profile</Link>
            </Button>
        </div>

        <ProfileBasicDetails :user="user" />
        <ProfileSecurityDetails :user="user" />
        <ProfileDateDetails :user="user" />
    </div>
</template>