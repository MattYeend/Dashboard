<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const city = defineModel<string>('city', { required: true });
const county = defineModel<string>('county', { required: true });
const postcode = defineModel<string>('postcode', { required: true });
const country = defineModel<string>('country', { required: true });
const isPrimary = defineModel<boolean>('isPrimary', { required: true });

interface Props {
    errors: Partial<
        Record<
            'city' | 'county' | 'postcode' | 'country' | 'is_primary',
            string
        >
    >;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
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
            <Label for="county">County</Label>
            <Input
                id="county"
                v-model="county"
                type="text"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.county" />
        </div>
        <div>
            <Label for="postcode">Postcode</Label>
            <Input
                id="postcode"
                v-model="postcode"
                type="text"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.postcode" />
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
        <div class="flex items-center space-x-2">
            <Checkbox id="is_primary" v-model:checked="isPrimary" />
            <Label for="is_primary">Set as primary address</Label>
            <InputError :message="errors.is_primary" />
        </div>
    </div>
</template>
