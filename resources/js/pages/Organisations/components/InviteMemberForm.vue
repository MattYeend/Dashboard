<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { RoleOption } from '@/types';

interface Props {
    organisationId: number;
    assignableRoles: RoleOption[];
}

const props = defineProps<Props>();

const form = useForm({
    email: '',
    invited_role: '',
});

function submit(): void {
    form.post(`/organisations/${props.organisationId}/invitations`, {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Invite a member</h2>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <Label for="email">Invite by email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    class="mt-1 block w-full"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <Label for="invited_role">Role</Label>
                <Select v-model="form.invited_role">
                    <SelectTrigger id="invited_role" class="mt-1 w-full">
                        <SelectValue placeholder="Select a role" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="role in props.assignableRoles"
                            :key="role.id"
                            :value="role.name"
                        >
                            {{ role.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.invited_role" />
            </div>

            <Button type="submit" :disabled="form.processing">
                Send invitation
            </Button>
        </form>
    </div>
</template>
