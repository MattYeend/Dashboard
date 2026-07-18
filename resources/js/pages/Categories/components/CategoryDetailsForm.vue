<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface Props {
    errors: Partial<Record<'name' | 'slug' | 'description', string>>;
}

defineProps<Props>();

const name = defineModel<string>('name', { required: true });
const description = defineModel<string>('description', { required: true });
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
            <Label for="description">Description</Label>
            <Textarea
                id="description"
                v-model="description"
                class="mt-1 block w-full"
                rows="4"
            />
            <InputError :message="errors.description" />
        </div>
    </div>
</template>
