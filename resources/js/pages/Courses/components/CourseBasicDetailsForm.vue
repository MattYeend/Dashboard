<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
 
defineProps<{
    errors: Partial<Record<string, string>>;
}>();
 
const title = defineModel<string>('title', { required: true });
const summary = defineModel<string>('summary', { required: true });
const description = defineModel<string>('description', { required: true });
</script>
 
<template>
    <div class="grid gap-4 border-b border-gray-500 pb-4">
        <div class="grid gap-2">
            <Label for="title">Title</Label>
            <Input id="title" v-model="title" maxlength="255" required />
            <InputError :message="errors.title" />
        </div>
 
        <div class="grid gap-2">
            <Label for="summary">Summary</Label>
            <Textarea id="summary" v-model="summary" maxlength="500" rows="2" />
            <InputError :message="errors.summary" />
        </div>
 
        <div class="grid gap-2">
            <Label for="description">Description</Label>
            <Textarea id="description" v-model="description" rows="8" />
            <InputError :message="errors.description" />
        </div>
    </div>
</template>