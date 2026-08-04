<script setup lang="ts">
defineProps<{
    role: 'user' | 'admin' | 'super_admin';
    roles: string[];
}>();
 
const tierLabels: Record<'user' | 'admin' | 'super_admin', string> = {
    user: 'User',
    admin: 'Admin',
    super_admin: 'Super Admin',
};
</script>
 
<template>
    <div>
        <h3 class="text-sm font-medium text-gray-400">Role details</h3>
        <dl class="mt-2 grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
            <div>
                <dt class="text-sm text-gray-400">Tier</dt>
                <dd class="text-gray-300">{{ tierLabels[role] }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-400">Additional roles</dt>
                <dd class="text-gray-300">
                    <span v-if="!roles.length">None assigned</span>
                    <span v-for="(roleName, index) in roles" :key="roleName">
                        {{ roleName }}<span v-if="index < roles.length - 1">, </span>
                    </span>
                </dd>
            </div>
        </dl>
    </div>
</template>