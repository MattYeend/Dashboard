<script setup lang="ts">
import type { InertiaFormProps } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface TagFormData {
    name: string;
    slug: string | null;
}

interface Props {
    errors: Partial<InertiaFormProps<TagFormData>['errors']>;
}

defineProps<Props>();

const name = defineModel<string>('name', { required: true });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="name"
                >Name <span class="text-destructive">*</span></Label
            >
            <Input
                id="name"
                v-model="name"
                type="text"
                class="mt-1 block w-full"
                placeholder="Enter tag name"
            />
            <InputError :message="errors.name" />
        </div>
    </div>
</template>
