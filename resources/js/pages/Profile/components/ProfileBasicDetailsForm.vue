<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    errors: Partial<Record<'name' | 'email', string>>;
}>();

const name = defineModel<string>('name', { required: true });
const email = defineModel<string>('email', { required: true });
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="grid gap-2">
            <Label for="profile-name">Name</Label>
            <Input id="profile-name" v-model="name" autocomplete="name" required />
            <InputError :message="errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="profile-email">Email</Label>
            <Input id="profile-email" v-model="email" type="email" autocomplete="email" required />
            <InputError :message="errors.email" />
        </div>
    </div>
</template>