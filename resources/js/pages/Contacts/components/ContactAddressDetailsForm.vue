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
            <label for="address" class="block text-sm font-medium text-grey-700">Address</label>
            <input id="address" v-model="form.address" type="text" class="mt-1 block w-full rounded-md border-grey-300 shadow-sm sm:text-sm" />
            <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="city" class="block text-sm font-medium text-grey-700">City</label>
                <input id="city" v-model="form.city" type="text" class="mt-1 block w-full rounded-md border-grey-300 shadow-sm sm:text-sm" />
                <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p>
            </div>
            <div>
                <label for="postal_code" class="block text-sm font-medium text-grey-700">Postal Code</label>
                <input id="postal_code" v-model="form.postal_code" type="text" class="mt-1 block w-full rounded-md border-grey-300 shadow-sm sm:text-sm" />
                <p v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600">{{ form.errors.postal_code }}</p>
            </div>
        </div>
        <div>
            <label for="country" class="block text-sm font-medium text-grey-700">Country</label>
            <input id="country" v-model="form.country" type="text" class="mt-1 block w-full rounded-md border-grey-300 shadow-sm sm:text-sm" />
            <p v-if="form.errors.country" class="mt-1 text-sm text-red-600">{{ form.errors.country }}</p>
        </div>
    </div>
</template>