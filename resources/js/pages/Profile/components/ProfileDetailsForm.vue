<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

defineProps<{
    errors: Partial<Record<'name' | 'email', string>>;
}>();

const name = defineModel<string>('name', { required: true });
const email = defineModel<string>('email', { required: true });
</script>

<template>
    <div class="space-y-4">
        <div class="space-y-2">
            <Label for="name">Name</Label>
            <Input
                id="name"
                v-model="name"
                type="text"
                autocomplete="name"
                required
            />
            <InputError :message="errors.name" />
        </div>

        <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input
                id="email"
                v-model="email"
                type="email"
                autocomplete="username"
                required
            />
            <InputError :message="errors.email" />
        </div>
    </div>
</template>
