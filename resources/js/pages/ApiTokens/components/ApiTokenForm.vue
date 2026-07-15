<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store as apiTokensStore } from '@/routes/api-tokens';

defineProps<{
    abilities: Record<string, string>;
}>();

const form = useForm({
    name: '',
    abilities: [] as string[],
    expires_at: null as string | null,
});

function isAbilitySelected(value: string): boolean {
    return form.abilities.includes(value);
}

function toggleAbility(value: string, checked: boolean | 'indeterminate'): void {
    const isChecked = checked === true;

    if (isChecked && !form.abilities.includes(value)) {
        form.abilities.push(value);

        return;
    }

    if (!isChecked) {
        form.abilities = form.abilities.filter((ability) => ability !== value);
    }
}

const expiresAt = computed<string | number | undefined>({
    get: () => form.expires_at ?? undefined,
    set: (value) => {
        form.expires_at = value === undefined || value === '' ? null : String(value);
    },
});

function submit(): void {
    form.post(apiTokensStore.url(), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <div class="space-y-2">
            <Label for="name">Token name</Label>
            <Input
                id="name"
                v-model="form.name"
                type="text"
                placeholder="e.g. Zapier integration"
            />
            <p v-if="form.errors.name" class="text-sm text-red-600">
                {{ form.errors.name }}
            </p>
        </div>

        <fieldset class="space-y-2">
            <legend class="text-sm font-medium text-gray-300">Abilities</legend>
            <div class="flex flex-wrap gap-4">
                <label
                    v-for="(label, value) in abilities"
                    :key="value"
                    class="flex items-center gap-2 text-sm"
                >
                    <Checkbox
                        :model-value="isAbilitySelected(value)"
                        @update:model-value="(checked) => toggleAbility(value, checked)"
                    />
                    {{ label }}
                </label>
            </div>
            <p v-if="form.errors.abilities" class="text-sm text-red-600">
                {{ form.errors.abilities }}
            </p>
        </fieldset>

        <div class="space-y-2">
            <Label for="expires_at">Expires (optional)</Label>
            <Input
                id="expires_at"
                v-model="expiresAt"
                type="datetime-local"
            />
            <p v-if="form.errors.expires_at" class="text-sm text-red-600">
                {{ form.errors.expires_at }}
            </p>
        </div>

        <Button type="submit" :disabled="form.processing">
            Create token
        </Button>
    </form>
</template>