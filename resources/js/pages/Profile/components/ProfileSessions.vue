<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { destroy } from '@/routes/profile/sessions';
import type { UserSession } from '@/types';

interface SessionsFormData {
    password: string;
}

defineProps<{
    sessions: UserSession[];
}>();

const form = useForm<SessionsFormData>({ password: '' });

function submit(): void {
    form.delete(destroy().url, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => form.reset('password'),
    });
}

function formatDateTime(value: string): string {
    return new Date(value).toLocaleString('en-GB');
}
</script>

<template>
    <section class="flex flex-col gap-4 rounded-lg border border-gray-500 p-4">
        <h2 class="text-sm font-medium text-gray-300">Browser sessions</h2>

        <p v-if="sessions.length === 0" class="text-sm text-gray-400">
            Session management needs the database session driver.
        </p>

        <ul v-else class="flex flex-col gap-3">
            <li v-for="(session, index) in sessions" :key="index" class="flex flex-col gap-1 border-b border-gray-500 pb-3">
                <span class="text-sm">
                    {{ session.user_agent || 'Unknown device' }}
                    <span v-if="session.is_current" class="text-xs text-gray-400">(this device)</span>
                </span>
                <span class="text-xs text-gray-400">
                    {{ session.ip_address || 'Unknown IP' }} · Last active {{ formatDateTime(session.last_active_at) }}
                </span>
            </li>
        </ul>

        <form v-if="sessions.length > 1" class="flex flex-col gap-3" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="sessions-password">Confirm your password</Label>
                <Input id="sessions-password" v-model="form.password" type="password" autocomplete="current-password" />
                <InputError :message="form.errors.password" />
            </div>
            <div>
                <Button type="submit" variant="outline" :disabled="form.processing">
                    Sign out other devices
                </Button>
            </div>
        </form>
    </section>
</template>