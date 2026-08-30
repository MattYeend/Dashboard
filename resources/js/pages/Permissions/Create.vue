<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import PermissionForm from '@/pages/Permissions/components/PermissionForm.vue';
import { store as permissionsStore } from '@/routes/permissions';

const form = useForm({
    name: '',
    guard_name: 'web',
});

function submit() {
    form.post(permissionsStore().url);
}
</script>

<template>
    <div>
        <h1 class="mb-6 text-lg font-semibold">Create permission</h1>

        <form @submit.prevent="submit" class="space-y-6">
            <PermissionForm
                v-model:name="form.name"
                v-model:guard-name="form.guard_name"
                :errors="form.errors"
            />

            <Button type="submit" :disabled="form.processing">Create</Button>
        </form>
    </div>
</template>
