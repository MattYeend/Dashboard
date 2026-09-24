<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import type { UserSession } from '@/types';

defineProps<{
    sessions: UserSession[];
}>();

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
            <li
                v-for="(session, index) in sessions"
                :key="index"
                class="flex flex-col gap-1 border-b border-gray-500 pb-3"
            >
                <span class="text-sm">
                    {{ session.user_agent || 'Unknown device' }}
                    <span
                        v-if="session.is_current"
                        class="text-xs text-gray-400"
                        >(this device)</span
                    >
                </span>
                <span class="text-xs text-gray-400">
                    {{ session.ip_address || 'Unknown IP' }} · Last active
                    {{ formatDateTime(session.last_active_at) }}
                </span>
            </li>
        </ul>

        <Form
            v-if="sessions.length > 1"
            v-bind="SecurityController.destroySessions.form()"
            reset-on-success
            :reset-on-error="['password']"
            class="flex flex-col gap-3"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="sessions-password">Confirm your password</Label>
                <PasswordInput
                    id="sessions-password"
                    name="password"
                    autocomplete="current-password"
                />
                <InputError :message="errors.password" />
            </div>
            <div>
                <Button type="submit" variant="outline" :disabled="processing">
                    Sign out other devices
                </Button>
            </div>
        </Form>
    </section>
</template>
