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

interface Props {
    errors?: Partial<Record<'type' | 'subject' | 'outcome' | 'occurredAt' | 'notes', string>>;
}

defineProps<Props>();

const type = defineModel<string>('type', { required: true });
const subject = defineModel<string>('subject', { required: true });
const outcome = defineModel<string>('outcome', { default: '' });
const occurredAt = defineModel<string>('occurredAt', { required: true });
const notes = defineModel<string>('notes', { default: '' });
</script>

<template>
    <div class="space-y-4">
        <div class="space-y-2">
            <Label for="type">Type</Label>
            <Select v-model="type">
                <SelectTrigger id="type">
                    <SelectValue placeholder="Select a type" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="call">Call</SelectItem>
                    <SelectItem value="email">Email</SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors?.type" />
        </div>

        <div class="space-y-2">
            <Label for="subject">Subject</Label>
            <Input id="subject" v-model="subject" type="text" />
            <InputError :message="errors?.subject" />
        </div>

        <div class="space-y-2">
            <Label for="occurred_at">Occurred at</Label>
            <Input id="occurred_at" v-model="occurredAt" type="date" />
            <InputError :message="errors?.occurredAt" />
        </div>

        <div class="space-y-2">
            <Label for="outcome">Outcome</Label>
            <Textarea id="outcome" v-model="outcome" rows="2" />
            <InputError :message="errors?.outcome" />
        </div>

        <div class="space-y-2">
            <Label for="notes">Notes</Label>
            <Textarea id="notes" v-model="notes" rows="4" />
            <InputError :message="errors?.notes" />
        </div>
    </div>
</template>
