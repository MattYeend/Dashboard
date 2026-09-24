<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { destroy, store } from '@/routes/profile/tokens';
import type { ApiToken } from '@/types';

interface TokenFormData {
    name: string;
    abilities: string[];
    expires_in_days: number;
}

const props = defineProps<{
    tokens: ApiToken[];
    abilities: string[];
    lifetimes: number[];
    newToken: string | null;
}>();

const form = useForm<TokenFormData>({
    name: '',
    abilities: [],
    expires_in_days: props.lifetimes[1] ?? props.lifetimes[0] ?? 30,
});

const confirmingId = ref<number | null>(null);
const copied = ref(false);

function submit(): void {
    form.post(store().url, {
        preserveScroll: true,
        onSuccess: () => form.reset('name', 'abilities'),
    });
}

function revoke(id: number): void {
    router.delete(destroy({ tokenId: id }).url, {
        preserveScroll: true,
        onFinish: () => {
            confirmingId.value = null;
        },
    });
}

async function copyToken(): Promise<void> {
    if (!props.newToken) {
        return;
    }

    await navigator.clipboard.writeText(props.newToken);
    copied.value = true;
}

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleDateString('en-GB') : 'Never';
}
</script>

<template>
    <section class="flex flex-col gap-4 rounded-lg border border-gray-500 p-4">
        <h2 class="text-sm font-medium text-gray-300">API tokens</h2>

        <div v-if="newToken" class="flex flex-col gap-2 rounded-lg border border-gray-500 p-3">
            <p class="text-sm">Copy your new token now. It will not be shown again.</p>
            <code class="break-all text-xs">{{ newToken }}</code>
            <div>
                <Button type="button" variant="outline" size="sm" @click="copyToken">
                    {{ copied ? 'Copied' : 'Copy token' }}
                </Button>
            </div>
        </div>

        <ul v-if="tokens.length > 0" class="flex flex-col gap-3">
            <li v-for="token in tokens" :key="token.id" class="flex items-center justify-between gap-3 border-b border-gray-500 pb-3">
                <div class="flex flex-col gap-1">
                    <span class="text-sm">{{ token.name }}</span>
                    <span class="text-xs text-gray-400">
                        {{ token.abilities.join(', ') }} · Expires {{ formatDate(token.expires_at) }} · Last used
                        {{ formatDate(token.last_used_at) }}
                    </span>
                </div>

                <div class="flex gap-2">
                    <template v-if="confirmingId === token.id">
                        <Button type="button" variant="destructive" size="sm" @click="revoke(token.id)">Confirm</Button>
                        <Button type="button" variant="outline" size="sm" @click="confirmingId = null">Cancel</Button>
                    </template>
                    <Button v-else type="button" variant="outline" size="sm" @click="confirmingId = token.id">
                        Revoke
                    </Button>
                </div>
            </li>
        </ul>
        <p v-else class="text-sm text-gray-400">You have no API tokens.</p>

        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="token-name">Token name</Label>
                <Input id="token-name" v-model="form.name" maxlength="100" />
                <InputError :message="form.errors.name" />
            </div>

            <fieldset class="grid gap-2">
                <legend class="text-sm font-medium">Abilities</legend>
                <label v-for="ability in abilities" :key="ability" class="flex items-center gap-2 text-sm">
                    <input v-model="form.abilities" type="checkbox" :value="ability" />
                    {{ ability }}
                </label>
                <InputError :message="form.errors.abilities" />
            </fieldset>

            <div class="grid gap-2">
                <Label for="token-expiry">Expires after</Label>
                <select
                    id="token-expiry"
                    v-model.number="form.expires_in_days"
                    class="rounded-md border border-gray-500 px-3 py-2 text-sm"
                >
                    <option v-for="days in lifetimes" :key="days" :value="days">{{ days }} days</option>
                </select>
                <InputError :message="form.errors.expires_in_days" />
            </div>

            <div>
                <Button type="submit" :disabled="form.processing">Create token</Button>
            </div>
        </form>
    </section>
</template>