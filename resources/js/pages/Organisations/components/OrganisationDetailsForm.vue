<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Props {
    errors: Partial<Record<'name' | 'slug', string>>;
}

defineProps<Props>();

const name = defineModel<string>('name', { required: true });
const slug = defineModel<string>('slug', { required: true });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="name">Name</Label>
            <Input
                id="name"
                v-model="name"
                type="text"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.name" />
        </div>
        <div>
            <Label for="slug">Slug</Label>
            <Input
                id="slug"
                v-model="slug"
                type="text"
                placeholder="Leave blank to generate automatically"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.slug" />
        </div>
    </div>
</template>
