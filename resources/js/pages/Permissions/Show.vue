<script setup lang="ts">
import PermissionAuditDetails from '@/pages/Permissions/components/PermissionAuditDetails.vue';
import PermissionBasicDetails from '@/pages/Permissions/components/PermissionBasicDetails.vue';
import PermissionRoleDetails from '@/pages/Permissions/components/PermissionRoleDetails.vue';
import type { Permission } from '@/types';

defineProps<{ permission: Permission }>();
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-lg font-semibold">{{ permission.name }}</h1>
        <PermissionBasicDetails :permission="permission" />
        <PermissionRoleDetails :permission="permission" />
        <PermissionAuditDetails :permission="permission" />
    </div>
</template>
