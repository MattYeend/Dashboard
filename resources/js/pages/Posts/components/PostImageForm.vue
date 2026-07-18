<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';

interface Props {
    errors: Partial<Record<'image', string>>;
    existingImage?: string | null;
}

defineProps<Props>();

const emit = defineEmits<{ 'update:file': [File | null] }>();

function onFileChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    emit('update:file', target.files?.[0] ?? null);
}
</script>

<template>
    <div>
        <Label for="image">Image</Label>
        <img
            v-if="existingImage"
            :src="existingImage"
            alt="Current post image"
            class="mt-2 h-24 w-24 rounded-md object-cover"
        />
        <input
            id="image"
            type="file"
            accept="image/*"
            class="mt-1 block w-full text-sm text-gray-300"
            @change="onFileChange"
        />
        <InputError :message="errors.image" />
    </div>
</template>
