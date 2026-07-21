<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

defineProps<{
    errors: Partial<Record<'name' | 'email' | 'phone' | 'company' | 'message', string>>;
    processing: boolean;
}>();

const name = defineModel<string>('name', { required: true });
const email = defineModel<string>('email', { required: true });
const phone = defineModel<string>('phone', { required: true });
const company = defineModel<string>('company', { required: true });
const message = defineModel<string>('message', { required: true });

const emit = defineEmits<{
    submit: [];
}>();
</script>

<template>
    <form class="mt-6 flex flex-col gap-4" @submit.prevent="emit('submit')">
        <div>
            <Label for="name">Name</Label>
            <Input id="name" v-model="name" type="text" required autofocus />
            <InputError :message="errors.name" />
        </div>

        <div>
            <Label for="email">Email</Label>
            <Input id="email" v-model="email" type="email" required />
            <InputError :message="errors.email" />
        </div>

        <div>
            <Label for="phone">Phone</Label>
            <Input id="phone" v-model="phone" type="text" />
            <InputError :message="errors.phone" />
        </div>

        <div>
            <Label for="company">Company</Label>
            <Input id="company" v-model="company" type="text" />
            <InputError :message="errors.company" />
        </div>

        <div>
            <Label for="message">Message</Label>
            <Textarea id="message" v-model="message" rows="4" />
            <InputError :message="errors.message" />
        </div>

        <button
            type="submit"
            class="rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal disabled:opacity-50 dark:border-[#3E3E3A]"
            :disabled="processing"
        >
            Register interest
        </button>
    </form>
</template>