<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

defineProps<{
    errors: Partial<Record<'title' | 'body', string>>;
}>();

const title = defineModel<string>('title', { required: true });
const body = defineModel<string>('body', { required: true });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="title">Title</Label>
            <Input id="title" v-model="title" type="text" />
            <InputError :message="errors.title" />
        </div>

        <div>
            <Label for="body">Message</Label>
            <Textarea id="body" v-model="body" rows="6" />
            <InputError :message="errors.body" />
        </div>
    </div>
</template>
