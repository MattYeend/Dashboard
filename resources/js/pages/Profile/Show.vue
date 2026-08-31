<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ProfileBasicDetails from './components/ProfileBasicDetails.vue';
import { edit } from '@/routes/profile';
import type { User } from '@/types';

defineOptions({ layout: { breadcrumbs: [{ title: 'Profile', href: '#' }] } });

const props = defineProps<{
    user: Pick<User, 'id' | 'name' | 'email' | 'role' | 'created_at'>;
    isOwnProfile: boolean;
}>();
</script>

<template>
    <div class="space-y-6">
        <ProfileBasicDetails :user="props.user" />

        <Link v-if="props.isOwnProfile" :href="edit().url">Edit profile</Link>
    </div>
</template>
