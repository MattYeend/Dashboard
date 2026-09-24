<script setup lang="ts">
import type { User } from '@/types';

defineProps<{
    user: Pick<User, 'id' | 'name' | 'email' | 'role' | 'created_at'>;
}>();
</script>

<template>
    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-xs text-gray-400">Name</dt>
            <dd class="text-sm">{{ user.name }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Email</dt>
            <dd class="text-sm">{{ user.email }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Role</dt>
            <dd class="text-sm">{{ user.role }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-400">Member since</dt>
            <dd class="text-sm">
                {{ new Date(user.created_at).toLocaleDateString('en-GB') }}
            </dd>
        </div>
    </dl>
</template>
