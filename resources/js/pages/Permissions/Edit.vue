<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import PermissionForm from '@/pages/Permissions/components/PermissionForm.vue';
import PermissionRolesAssignForm from '@/pages/Permissions/components/PermissionRolesAssignForm.vue';
import { 
    update as permissionsUpdate, 
    assignRoles as permissionsAssignRoles
} from '@/routes/permissions';
import type { Permission, RoleOption } from '@/types';

interface Props {
    permission: Permission;
    roles: RoleOption[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.permission.name,
    guard_name: props.permission.guard_name,
});

const rolesForm = useForm({
    role_ids: (props.permission.roles ?? []).map((role) => role.id),
});

function submit() {
    form.put(permissionsUpdate(props.permission.id).url);
}

function submitRoles() {
    rolesForm.patch(permissionsAssignRoles(props.permission.id).url);
}
</script>

<template>
    <div class="space-y-10">
        <div>
            <h1 class="mb-6 text-lg font-semibold">Edit permission</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <PermissionForm v-model:name="form.name" v-model:guard-name="form.guard_name" :errors="form.errors" />
                <Button type="submit" :disabled="form.processing">Save</Button>
            </form>
        </div>

        <div>
            <h2 class="mb-4 text-base font-semibold">Assign to roles</h2>
            <form @submit.prevent="submitRoles" class="space-y-4">
                <PermissionRolesAssignForm v-model:selected-role-ids="rolesForm.role_ids" :roles="props.roles" />
                <Button type="submit" :disabled="rolesForm.processing">Save role assignments</Button>
            </form>
        </div>
    </div>
</template>
