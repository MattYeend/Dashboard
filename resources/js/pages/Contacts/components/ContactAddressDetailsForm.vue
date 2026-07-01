<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
            <Label for="address">Address</Label>
            <Input
                id="address"
                v-model="address"
                type="text"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.address" />
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <Label for="city">City</Label>
                <Input
                    id="city"
                    v-model="city"
                    type="text"
                    class="mt-1 block w-full"
                />
                <InputError :message="errors.city" />
            </div>
            <div>
                <Label for="postal_code">Postal Code</Label>
                <Input
                    id="postal_code"
                    v-model="postalCode"
                    type="text"
                    class="mt-1 block w-full"
                />
                <InputError :message="errors.postal_code" />
            </div>
        </div>
        <div>
            <Label for="country">Country</Label>
            <Input
                id="country"
                v-model="country"
                type="text"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.country" />
        </div>
    </div>
</template>
