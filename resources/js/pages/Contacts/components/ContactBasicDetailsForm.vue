<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';

interface ContactFormData {
    phone: string;
    email: string;
    address: string;
    city: string;
    postal_code: string;
    country: string;
}

defineProps<{ form: InertiaForm<ContactFormData> }>();
</script>

<template>
    <div class="space-y-4">
        <div>
            <label for="email" class="block text-sm font-medium text-grey-700">Email Address</label>
            <input id="email" v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-grey-300 shadow-sm sm:text-sm" />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
        </div>
        <div>
            <label for="phone" class="block text-sm font-medium text-grey-700">Phone</label>
            <input id="phone" v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-grey-300 shadow-sm sm:text-sm" />
            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
        </div>
    </div>
</template>