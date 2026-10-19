<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
 
defineProps<{
    errors: Partial<Record<string, string>>;
}>();
 
const content = defineModel<string>('content', { required: true });
const videoUrl = defineModel<string>('videoUrl', { required: true });
</script>
 
<template>
    <div class="grid gap-4 border-b border-gray-500 pb-4">
        <div class="grid gap-2">
            <Label for="content">Content</Label>
            <Textarea id="content" v-model="content" rows="12" />
            <InputError :message="errors.content" />
        </div>
 
        <div class="grid gap-2">
            <Label for="video-url">Video link</Label>
            <Input id="video-url" v-model="videoUrl" type="url" placeholder="https://www.youtube.com/watch?v=..." />
            <p class="text-xs text-gray-400">YouTube and Vimeo links only.</p>
            <InputError :message="errors.video_url" />
        </div>
    </div>
</template>