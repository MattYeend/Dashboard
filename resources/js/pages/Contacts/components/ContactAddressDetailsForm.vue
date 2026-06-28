<script setup lang="ts">
const address = defineModel<string>('address', { required: true });
const city = defineModel<string>('city', { required: true });
const postalCode = defineModel<string>('postalCode', { required: true });
const country = defineModel<string>('country', { required: true });

interface Props {
    errors: Partial<
        Record<'address' | 'city' | 'postal_code' | 'country', string>
    >;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
        <div>
            <label for="address" class="block text-sm font-medium text-gray-700"
                >Address</label
            >
            <input
                id="address"
                v-model="address"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
            />
            <p v-if="errors.address" class="mt-1 text-sm text-red-600">
                {{ errors.address }}
            </p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label
                    for="city"
                    class="block text-sm font-medium text-gray-700"
                    >City</label
                >
                <input
                    id="city"
                    v-model="city"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
                />
                <p v-if="errors.city" class="mt-1 text-sm text-red-600">
                    {{ errors.city }}
                </p>
            </div>
            <div>
                <label
                    for="postal_code"
                    class="block text-sm font-medium text-gray-700"
                    >Postal Code</label
                >
                <input
                    id="postal_code"
                    v-model="postalCode"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
                />
                <p v-if="errors.postal_code" class="mt-1 text-sm text-red-600">
                    {{ errors.postal_code }}
                </p>
            </div>
        </div>
        <div>
            <label for="country" class="block text-sm font-medium text-gray-700"
                >Country</label
            >
            <input
                id="country"
                v-model="country"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
            />
            <p v-if="errors.country" class="mt-1 text-sm text-red-600">
                {{ errors.country }}
            </p>
        </div>
    </div>
</template>
