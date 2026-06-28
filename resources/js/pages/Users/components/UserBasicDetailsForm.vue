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
            <label for="name" class="text-gray-700 block text-sm font-medium"
                >Name</label
            >
            <input
                id="name"
                v-model="name"
                type="text"
                class="border-gray-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">
                {{ errors.name }}
            </p>
        </div>

        <div>
            <label for="email" class="text-gray-700 block text-sm font-medium"
                >Email Address</label
            >
            <input
                id="email"
                v-model="email"
                type="email"
                class="border-gray-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                {{ errors.email }}
            </p>
        </div>

        <div>
            <label
                for="password"
                class="text-gray-700 block text-sm font-medium"
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
                class="border-gray-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">
                {{ errors.password }}
            </p>
        </div>

        <div>
            <label
                for="password_confirmation"
                class="text-gray-700 block text-sm font-medium"
                >Confirm Password</label
            >
            <input
                id="password_confirmation"
                v-model="passwordConfirmation"
                type="password"
                class="border-gray-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
            />
        </div>
    </div>
</template>
