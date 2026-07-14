<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const addressLineOne = defineModel<string>('addressLineOne', {
    required: true,
});
const addressLineTwo = defineModel<string>('addressLineTwo', {
    required: true,
});
const town = defineModel<string>('town', { required: true });

interface Props {
    errors: Partial<
        Record<'address_line_one' | 'address_line_two' | 'town', string>
    >;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="address_line_one">Address line one</Label>
            <Input
                id="address_line_one"
                v-model="addressLineOne"
                type="text"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.address_line_one" />
        </div>
        <div>
            <Label for="address_line_two">Address line two</Label>
            <Input
                id="address_line_two"
                v-model="addressLineTwo"
                type="text"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.address_line_two" />
        </div>
        <div>
            <Label for="town">Town</Label>
            <Input
                id="town"
                v-model="town"
                type="text"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.town" />
        </div>
    </div>
</template>
