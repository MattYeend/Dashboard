<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox';
import Label from '@/components/ui/label/Label.vue';
import type { RoleOption } from '@/types';

interface Props {
    roles: RoleOption[];
}

defineProps<Props>();

const selectedRoleIds = defineModel<number[]>('selectedRoleIds', {
    required: true,
});

function toggle(roleId: number, checked: boolean) {
    if (checked) {
        if (!selectedRoleIds.value.includes(roleId)) {
            selectedRoleIds.value = [...selectedRoleIds.value, roleId];
        }
    } else {
        selectedRoleIds.value = selectedRoleIds.value.filter(
            (id) => id !== roleId,
        );
    }
}
</script>

<template>
    <div class="space-y-2">
        <Label>Assigned roles</Label>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
            <div
                v-for="role in roles"
                :key="role.id"
                class="flex items-center gap-2"
            >
                <Checkbox
                    :id="`role-${role.id}`"
                    :model-value="selectedRoleIds.includes(role.id)"
                    @update:model-value="
                        (checked) => toggle(role.id, Boolean(checked))
                    "
                />
                <Label :for="`role-${role.id}`" class="text-sm font-normal">
                    {{ role.name }}
                </Label>
            </div>
        </div>
    </div>
</template>
