<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AddressLineDetailsForm from '@/pages/Addresses/components/AddressLineDetailsForm.vue';
import AddressLocationDetailsForm from '@/pages/Addresses/components/AddressLocationDetailsForm.vue';
import AddressTypeForm from '@/pages/Addresses/components/AddressTypeForm.vue';
import { index as addressesIndex } from '@/routes/addresses';

interface AddressFormData {
    addressable_type: string;
    addressable_id: number | null;
    address_line_one: string;
    address_line_two: string;
    town: string;
    city: string;
    county: string;
    postcode: string;
    country: string;
    is_primary: boolean;
}

interface Props {
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<AddressFormData>['errors']>;
    addressableTypes: { value: string; label: string }[];
    addressableOptions: { value: number; label: string }[];
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const addressableType = defineModel<string>('addressableType', {
    required: true,
});
const addressableId = defineModel<number | null>('addressableId', {
    required: true,
});
const addressLineOne = defineModel<string>('addressLineOne', {
    required: true,
});
const addressLineTwo = defineModel<string>('addressLineTwo', {
    required: true,
});
const town = defineModel<string>('town', { required: true });
const city = defineModel<string>('city', { required: true });
const county = defineModel<string>('county', { required: true });
const postcode = defineModel<string>('postcode', { required: true });
const country = defineModel<string>('country', { required: true });
const isPrimary = defineModel<boolean>('isPrimary', { required: true });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <AddressTypeForm
            v-model:addressable-type="addressableType"
            v-model:addressable-id="addressableId"
            :addressable-types="addressableTypes"
            :addressable-options="addressableOptions"
            :errors="errors"
        />

        <AddressLineDetailsForm
            v-model:address-line-one="addressLineOne"
            v-model:address-line-two="addressLineTwo"
            v-model:town="town"
            :errors="errors"
        />

        <AddressLocationDetailsForm
            v-model:city="city"
            v-model:county="county"
            v-model:postcode="postcode"
            v-model:country="country"
            v-model:is-primary="isPrimary"
            :errors="errors"
        />

        <div class="flex justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="addressesIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Address' : 'Create Address' }}
            </Button>
        </div>
    </form>
</template>
