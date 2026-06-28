<script setup lang="ts">
const role = defineModel<string>('role', { required: true });

interface Props {
    errors: Partial<Record<'role', string>>;
}

defineProps<Props>();

const roles = [
    { value: 'user', label: 'User' },
    { value: 'admin', label: 'Admin' },
    { value: 'super_admin', label: 'Super Admin' },
];
</script>

<template>
    <div>
        <label for="role" class="block text-sm font-medium text-gray-400"
            >Role</label
        >
        <select
            id="role"
            v-model="role"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
        >
            <option
                v-for="roleOption in roles"
                :key="roleOption.value"
                :value="roleOption.value"
            >
                {{ roleOption.label }}
            </option>
        </select>
        <p v-if="errors.role" class="mt-1 text-sm text-red-600">
            {{ errors.role }}
        </p>
    </div>
</template>
