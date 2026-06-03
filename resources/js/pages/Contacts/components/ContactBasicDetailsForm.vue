<script setup lang="ts">
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
            <label for="email" class="text-grey-700 block text-sm font-medium"
                >Email Address</label
            >
            <input
                id="email"
                v-model="email"
                type="email"
                class="border-grey-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                {{ errors.email }}
            </p>
        </div>
        <div>
            <label for="phone" class="text-grey-700 block text-sm font-medium"
                >Phone</label
            >
            <input
                id="phone"
                v-model="phone"
                type="text"
                class="border-grey-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
            />
            <p v-if="errors.phone" class="mt-1 text-sm text-red-600">
                {{ errors.phone }}
            </p>
        </div>
    </div>
</template>
