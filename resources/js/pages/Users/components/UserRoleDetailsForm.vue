<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

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
        <Label for="role">Role</Label>
        <Select v-model="role">
            <SelectTrigger id="role" class="mt-1 w-full">
                <SelectValue placeholder="Select a role" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="roleOption in roles"
                    :key="roleOption.value"
                    :value="roleOption.value"
                >
                    {{ roleOption.label }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.role" />
    </div>
</template>
