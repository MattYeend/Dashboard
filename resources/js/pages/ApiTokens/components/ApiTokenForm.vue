<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

defineProps<{
    abilities: Record<string, string>;
}>();

const form = useForm({
    name: '',
    abilities: [] as string[],
    expires_at: null as string | null,
});

const submit = () => {
    form.post(route('api-tokens.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <div>
            <label for="name">Token name</label>
            <input
                id="name"
                v-model="form.name"
                type="text"
                placeholder="e.g. Zapier integration"
            />
            <p v-if="form.errors.name">{{ form.errors.name }}</p>
        </div>

        <fieldset>
            <legend>Abilities</legend>
            <label v-for="(label, value) in abilities" :key="value">
                <input
                    type="checkbox"
                    :value="value"
                    v-model="form.abilities"
                />
                {{ label }}
            </label>
            <p v-if="form.errors.abilities">{{ form.errors.abilities }}</p>
        </fieldset>

        <div>
            <label for="expires_at">Expires (optional)</label>
            <input
                id="expires_at"
                v-model="form.expires_at"
                type="datetime-local"
            />
            <p v-if="form.errors.expires_at">{{ form.errors.expires_at }}</p>
        </div>

        <button type="submit" :disabled="form.processing">Create token</button>
    </form>
</template>
