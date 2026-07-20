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
const slug = defineModel<string | null>('slug', { default: null });
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

        <div>
            <Label for="slug">Slug</Label>
            <Input
                id="slug"
                :model-value="slug ?? ''"
                type="text"
                class="mt-1 block w-full"
                placeholder="Auto-generated if left blank"
                @update:model-value="slug = ($event as string) || null"
            />
            <InputError :message="errors.slug" />
        </div>
    </div>
</template>
