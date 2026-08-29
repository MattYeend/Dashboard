<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

defineProps<{
    errors: Partial<Record<'audience_type' | 'audience_ids', string>>;
}>();

const audienceType = defineModel<'all' | 'role' | 'users'>('audienceType', {
    required: true,
});
const audienceIds = defineModel<string>('audienceIds', { required: true });
</script>

<template>
    <div class="space-y-4">
        <div>
            <Label for="audience_type">Send to</Label>
            <Select v-model="audienceType">
                <SelectTrigger id="audience_type">
                    <SelectValue placeholder="Select audience" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Everyone</SelectItem>
                    <SelectItem value="role">Specific role(s)</SelectItem>
                    <SelectItem value="users">Specific user(s)</SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.audience_type" />
        </div>

        <div v-if="audienceType !== 'all'">
            <Label for="audience_ids">
                {{
                    audienceType === 'role'
                        ? 'Role names (comma separated)'
                        : 'User IDs (comma separated)'
                }}
            </Label>
            <Textarea id="audience_ids" v-model="audienceIds" rows="2" />
            <InputError :message="errors.audience_ids" />
        </div>
    </div>
</template>
