<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const email = defineModel<string>('email', { required: true });
const phone = defineModel<string>('phone', { required: true });

interface Props {
    errors: Partial<Record<'email' | 'phone', string>>;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="email">Email Address</Label>
            <Input
                id="email"
                v-model="email"
                type="email"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.email" />
        </div>
        <div>
            <Label for="phone">Phone</Label>
            <Input
                id="phone"
                v-model="phone"
                type="text"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.phone" />
        </div>
    </div>
</template>
