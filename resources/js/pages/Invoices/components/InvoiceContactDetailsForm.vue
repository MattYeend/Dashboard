<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type {
    InvoiceContactFormData,
    InvoiceFormData,
} from '@/types/forms';

interface Props {
    errors: Partial<InertiaFormProps<InvoiceFormData>['errors']>;
}

defineProps<Props>();

const contact = defineModel<InvoiceContactFormData>('contact', { required: true });

function updateField(field: keyof InvoiceContactFormData, value: string): void {
    contact.value = {
        ...contact.value,
        [field]: value || null,
    };
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="contact_phone">Phone</Label>
            <Input
                id="contact_phone"
                :model-value="contact.phone ?? ''"
                type="text"
                class="mt-1 block w-full"
                @update:model-value="updateField('phone', $event as string)"
            />
            <InputError :message="errors['contact.phone']" />
        </div>

        <div>
            <Label for="contact_email">Email</Label>
            <Input
                id="contact_email"
                :model-value="contact.email ?? ''"
                type="email"
                class="mt-1 block w-full"
                @update:model-value="updateField('email', $event as string)"
            />
            <InputError :message="errors['contact.email']" />
        </div>

        <div>
            <Label for="contact_address">Address</Label>
            <Input
                id="contact_address"
                :model-value="contact.address ?? ''"
                type="text"
                class="mt-1 block w-full"
                @update:model-value="updateField('address', $event as string)"
            />
            <InputError :message="errors['contact.address']" />
        </div>

        <div>
            <Label for="contact_city">City</Label>
            <Input
                id="contact_city"
                :model-value="contact.city ?? ''"
                type="text"
                class="mt-1 block w-full"
                @update:model-value="updateField('city', $event as string)"
            />
            <InputError :message="errors['contact.city']" />
        </div>

        <div>
            <Label for="contact_postal_code">Postal Code</Label>
            <Input
                id="contact_postal_code"
                :model-value="contact.postal_code ?? ''"
                type="text"
                class="mt-1 block w-full"
                @update:model-value="
                    updateField('postal_code', $event as string)
                "
            />
            <InputError :message="errors['contact.postal_code']" />
        </div>

        <div>
            <Label for="contact_country">Country</Label>
            <Input
                id="contact_country"
                :model-value="contact.country ?? ''"
                type="text"
                class="mt-1 block w-full"
                @update:model-value="updateField('country', $event as string)"
            />
            <InputError :message="errors['contact.country']" />
        </div>
    </div>
</template>
