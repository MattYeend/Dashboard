<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';

interface ContactTypeFormData {
    contactable_type: string;
    contactable_id: number | null;
}

interface Props {
    errors: Partial<InertiaFormProps<ContactTypeFormData>['errors']>;

    contactableTypes: { value: string; label: string }[];
    contactableOptions: { value: number; label: string }[];
}

defineProps<Props>();

const contactableType = defineModel<string>('contactableType', {
    required: true,
});
const contactableId = defineModel<number | null>('contactableId', {
    required: true,
});
</script>

<template>
    <!-- TYPE -->
    <div>
        <label class="block text-sm font-medium"> Contact type </label>

        <select v-model="contactableType" required>
            <option value="" disabled>Select type</option>
            <option
                v-for="type in contactableTypes"
                :key="type.value"
                :value="type.value"
            >
                {{ type.label }}
            </option>
        </select>
        <p v-if="errors.contactable_type" class="text-red-600">
            {{ errors.contactable_type }}
        </p>
    </div>

    <!-- OWNER -->
    <div>
        <label class="block text-sm font-medium"> Contact owner </label>

        <select v-model="contactableId" :disabled="!contactableType">
            <option :value="null" disabled>Select owner</option>

            <option
                v-for="option in contactableOptions"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>

        <p v-if="errors.contactable_id" class="text-red-600">
            {{ errors.contactable_id }}
        </p>
    </div>
</template>