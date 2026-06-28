<script setup lang="ts">
const name = defineModel<string>('name', { required: true });
const email = defineModel<string>('email', { required: true });
const password = defineModel<string>('password', { required: true });
const passwordConfirmation = defineModel<string>('passwordConfirmation', {
    required: true,
});

interface Props {
    isEditing: boolean;
    errors: Partial<
        Record<'name' | 'email' | 'password' | 'password_confirmation', string>
    >;
}

defineProps<Props>();
</script>

<template>
    <div class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700"
                >Name</label
            >
            <input
                id="name"
                v-model="name"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">
                {{ errors.name }}
            </p>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700"
                >Email Address</label
            >
            <input
                id="email"
                v-model="email"
                type="email"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                {{ errors.email }}
            </p>
        </div>

        <div>
            <label
                for="password"
                class="block text-sm font-medium text-gray-700"
            >
                {{
                    isEditing
                        ? 'New Password (leave blank to keep current)'
                        : 'Password'
                }}
            </label>
            <input
                id="password"
                v-model="password"
                type="password"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">
                {{ errors.password }}
            </p>
        </div>

        <div>
            <label
                for="password_confirmation"
                class="block text-sm font-medium text-gray-700"
                >Confirm Password</label
            >
            <input
                id="password_confirmation"
                v-model="passwordConfirmation"
                type="password"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm"
            />
        </div>
    </div>
</template>
