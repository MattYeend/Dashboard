<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import OrganisationDetailsForm from '@/pages/Organisations/components/OrganisationDetailsForm.vue';
import { index as organisationsIndex } from '@/routes/organisations';

interface OrganisationFormData {
    name: string;
    slug: string;
}

interface Props {
    isEditing: boolean;
    processing: boolean;
    errors: Partial<InertiaFormProps<OrganisationFormData>['errors']>;
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const name = defineModel<string>('name', { required: true });
const slug = defineModel<string>('slug', { required: true });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <OrganisationDetailsForm
            v-model:name="name"
            v-model:slug="slug"
            :errors="errors"
        />

        <div class="flex justify-end space-x-3">
            <Button as-child variant="outline">
                <Link :href="organisationsIndex.url()">Cancel</Link>
            </Button>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Organisation' : 'Create Organisation' }}
            </Button>
        </div>
    </form>
</template>
