<script setup lang="ts">
import type { User } from '@/types';

interface Props {
    user: User;
}

defineProps<Props>();

const tierLabels: Record<User['role'], string> = {
    user: 'User',
    admin: 'Admin',
    super_admin: 'Super Admin',
};
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Role details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Tier</dt>
                <dd class="text-sm">{{ tierLabels[user.role] }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Additional roles</dt>
                <dd class="text-sm">
                    <span v-if="!user.roles.length">None assigned</span>
                    <span
                        v-for="(roleName, index) in user.roles"
                        :key="roleName"
                    >
                        {{ roleName }}
                        <span v-if="index < user.roles.length - 1">, </span>
                    </span>
                </dd>
            </div>
        </dl>
    </div>
</template>
