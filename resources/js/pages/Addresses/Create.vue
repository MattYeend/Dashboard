<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch } from 'vue';
import { nullIfBlank } from '@/lib/forms';
import AddressForm from '@/pages/Addresses/components/AddressForm.vue';
import { store as addressesStore } from '@/routes/addresses';

interface Props {
    addressableTypes: { value: string; label: string }[];
}

const props = defineProps<Props>();

const form = useForm({
    addressable_type: '',
    addressable_id: null as number | null,
    address_line_one: '',
    address_line_two: '',
    town: '',
    city: '',
    county: '',
    postcode: '',
    country: '',
    is_primary: false,
});

interface AddressableOption {
    value: number;
    label: string;
}

const addressableOptions = ref<AddressableOption[]>([]);

watch(
    () => form.addressable_type,
    async (type: string) => {
        if (!type) {
            addressableOptions.value = [];
            form.addressable_id = null;

            return;
        }

        const res = await axios.get('/addresses/addressable-options', {
            params: { type },
        });

        addressableOptions.value = res.data;
        form.addressable_id = null;
    },
);

function submit(): void {
    form.transform((data) => ({
        ...data,
        address_line_two: nullIfBlank(data.address_line_two),
        town: nullIfBlank(data.town),
        county: nullIfBlank(data.county),
        postcode: nullIfBlank(data.postcode),
    })).post(addressesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold">Create Address</h1>

            <AddressForm
                v-model:addressable-type="form.addressable_type"
                v-model:addressable-id="form.addressable_id"
                v-model:address-line-one="form.address_line_one"
                v-model:address-line-two="form.address_line_two"
                v-model:town="form.town"
                v-model:city="form.city"
                v-model:county="form.county"
                v-model:postcode="form.postcode"
                v-model:country="form.country"
                v-model:is-primary="form.is_primary"
                :addressable-types="props.addressableTypes"
                :addressable-options="addressableOptions"
                :is-editing="false"
                :processing="form.processing"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
