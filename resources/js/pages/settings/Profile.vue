<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <Form v-bind="ProfileController.update.form()" class="space-y-6" v-slot="{ errors, processing, recentlySuccessful }">
                    <div class="grid gap-2">
                        <Label for="title">Title</Label>
                        <select
                            id="title"
                            name="title"
                            :default-value="user.title"
                            required
                            autocomplete="title"
                            class="border rounded-md px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300"
                        >
                            <option value="" disabled selected>Select a title</option>
                            <option value="Mr" :selected="user.title ==='Mr'">Mr.</option>
                            <option value="Ms" :selected="user.title ==='Ms'">Ms.</option>
                            <option value="Mrs" :selected="user.title ==='Mrs'">Mrs.</option>
                            <option value="Dr" :selected="user.title ==='Dr'">Dr.</option>
                            <option value="Prof" :selected="user.title ==='Prof'">Prof.</option>
                        </select>
                        <InputError :message="errors.title" />
                    </div>
                    
                    <div class="grid gap-2">
                        <Label for="name">First Name</Label>
                        <Input
                            id="first_name"
                            class="mt-1 block w-full"
                            name="first_name"
                            :default-value="user.first_name"
                            required
                            autocomplete="first_name"
                            placeholder="First name"
                        />
                        <InputError class="mt-2" :message="errors.first_name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="name">Middle Name</Label>
                        <Input
                            id="first_name"
                            class="mt-1 block w-full"
                            name="middle_name"
                            :default-value="user.middle_name"
                            autocomplete="middle_name"
                            placeholder="Middle name"
                        />
                        <InputError class="mt-2" :message="errors.middle_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Last Name</Label>
                        <Input
                            id="last_name"
                            class="mt-1 block w-full"
                            name="last_name"
                            :default-value="user.last_name"
                            required
                            autocomplete="last_name"
                            placeholder="Last name"
                        />
                        <InputError class="mt-2" :message="errors.last_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing">Save</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
