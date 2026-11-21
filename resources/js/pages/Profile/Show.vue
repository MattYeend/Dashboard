<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import ProfileBasicDetails from '@/pages/Profile/components/ProfileBasicDetails.vue';
import { edit as profileEdit } from '@/routes/profile';
import type { User } from '@/types';

defineOptions({ layout: { breadcrumbs: [{ title: 'Profile', href: '#' }] } });

interface Props {
    user: Pick<User, 'id' | 'name' | 'email' | 'role' | 'created_at'>;
    isOwnProfile: boolean;
}

defineProps<Props>();
</script>

<template>
    <Head :title="user.name" />

    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ user.name }}
                </h1>
                <div v-if="isOwnProfile" class="ml-auto flex flex-wrap gap-2">
                    <Button as-child>
                        <Link :href="profileEdit.url()">Edit profile</Link>
                    </Button>
                </div>
            </div>

            <div class="space-y-6">
                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">Basic details</h2>
                    <ProfileBasicDetails :user="user" />
                </section>
            </div>
        </div>
    </div>
</template>
