<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface RegisterInterestFormData {
    name: string;
    email: string;
    phone: string;
    company: string;
    message: string;
}

defineProps<{
    form: InertiaForm<RegisterInterestFormData>;
}>();

const emit = defineEmits<{
    submit: [];
}>();
</script>

<template>
    <form class="mt-6 flex flex-col gap-4" @submit.prevent="emit('submit')">
        <div>
            <Label for="name">Name</Label>
            <Input id="name" v-model="form.name" type="text" required autofocus />
            <InputError :message="form.errors.name" />
        </div>

        <div>
            <Label for="email">Email</Label>
            <Input id="email" v-model="form.email" type="email" required />
            <InputError :message="form.errors.email" />
        </div>

        <div>
            <Label for="phone">Phone</Label>
            <Input id="phone" v-model="form.phone" type="text" />
            <InputError :message="form.errors.phone" />
        </div>

        <div>
            <Label for="company">Company</Label>
            <Input id="company" v-model="form.company" type="text" />
            <InputError :message="form.errors.company" />
        </div>

        <div>
            <Label for="message">Message</Label>
            <Textarea id="message" v-model="form.message" rows="4" />
            <InputError :message="form.errors.message" />
        </div>

        <button
            type="submit"
            class="rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal disabled:opacity-50 dark:border-[#3E3E3A]"
            :disabled="form.processing"
        >
            Register interest
        </button>
    </form>
</template>