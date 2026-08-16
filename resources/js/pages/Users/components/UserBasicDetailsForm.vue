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

const name = defineModel<string>('name', { required: true });
const email = defineModel<string>('email', { required: true });
const password = defineModel<string>('password', { required: true });
const passwordConfirmation = defineModel<string>('passwordConfirmation', {
    required: true,
});
const locale = defineModel<string>('locale', { required: true });

interface Props {
    isEditing: boolean;
    availableLocales: Record<string, string>;
    errors: Partial<
        Record<
            'name' | 'email' | 'password' | 'password_confirmation' | 'locale',
            string
        >
    >;
}

defineProps<Props>();
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
            <Label for="email">Email Address</Label>
            <Input
                id="email"
                v-model="email"
                type="email"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.email" />
        </div>

        <div>
            <Label for="password">
                {{
                    isEditing
                        ? 'New Password (leave blank to keep current)'
                        : 'Password'
                }}
            </Label>
            <Input
                id="password"
                v-model="password"
                type="password"
                class="mt-1 block w-full"
            />
            <InputError :message="errors.password" />
        </div>

        <div>
            <Label for="password_confirmation">Confirm Password</Label>
            <Input
                id="password_confirmation"
                v-model="passwordConfirmation"
                type="password"
                class="mt-1 block w-full"
            />
        </div>

        <div>
            <Label for="locale">Locale</Label>
            <Select v-model="locale">
                <SelectTrigger id="locale" class="mt-1 w-full">
                    <SelectValue placeholder="Select a locale" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="(label, value) in availableLocales"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.locale" />
        </div>
    </div>
</template>
