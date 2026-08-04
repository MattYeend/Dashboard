<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
 
const role = defineModel<string>('role', { required: true });
const roles = defineModel<string[]>('roles', { required: true });
 
interface Props {
    availableRoles: string[];
    errors: Partial<Record<'role' | 'roles', string>>;
}
 
defineProps<Props>();
 
const tierRoleOptions = [
    { value: 'user', label: 'User' },
    { value: 'admin', label: 'Admin' },
    { value: 'super_admin', label: 'Super Admin' },
];
 
function toggleFunctionalRole(roleName: string, checked: boolean): void {
    roles.value = checked
        ? [...roles.value, roleName]
        : roles.value.filter((r) => r !== roleName);
}
</script>
 
<template>
    <div class="space-y-6">
        <div>
            <Label for="role">Role</Label>
            <Select v-model="role">
                <SelectTrigger id="role" class="mt-1 w-full">
                    <SelectValue placeholder="Select a role" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="roleOption in tierRoleOptions"
                        :key="roleOption.value"
                        :value="roleOption.value"
                    >
                        {{ roleOption.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.role" />
        </div>
 
        <div class="space-y-3">
            <Label>Additional roles</Label>
            <div class="grid grid-cols-2 gap-2">
                <div
                    v-for="roleName in availableRoles"
                    :key="roleName"
                    class="flex items-center gap-2"
                >
                    <Checkbox
                        :id="`role-${roleName}`"
                        :model-value="roles.includes(roleName)"
                        @update:model-value="
                            (checked) => toggleFunctionalRole(roleName, !!checked)
                        "
                    />
                    <Label :for="`role-${roleName}`" class="font-normal text-gray-300">
                        {{ roleName }}
                    </Label>
                </div>
            </div>
            <InputError :message="errors.roles" />
        </div>
    </div>
</template>