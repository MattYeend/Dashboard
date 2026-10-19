<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { CourseOption } from '@/types';
 
defineProps<{
    errors: Partial<Record<string, string>>;
    courses: CourseOption[];
}>();
 
const courseId = defineModel<number | null>('courseId', { required: true });
const title = defineModel<string>('title', { required: true });
const summary = defineModel<string>('summary', { required: true });
</script>
 
<template>
    <div class="grid gap-4 border-b border-gray-500 pb-4">
        <div class="grid gap-2">
            <Label for="course">Course</Label>
            <Select v-model="courseId">
                <SelectTrigger id="course">
                    <SelectValue placeholder="Select course" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="course in courses" :key="course.id" :value="course.id">
                        {{ course.title }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.course_id" />
        </div>
 
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
    </div>
</template>