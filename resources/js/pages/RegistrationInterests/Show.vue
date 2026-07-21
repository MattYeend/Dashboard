<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { RegistrationInterest } from '@/types';
import RegistrationInterestAuditDetails from './components/RegistrationInterestAuditDetails.vue';
import RegistrationInterestBasicDetails from './components/RegistrationInterestBasicDetails.vue';
import RegistrationInterestMessageDetails from './components/RegistrationInterestMessageDetails.vue';
import { index as registrationInterestsIndex } from '@/routes/registration-interests';

defineProps<{
    interest: RegistrationInterest;
}>();
</script>

<template>
    <Head title="Registration interest" />

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">
                    Registration interest
                </h1>
                <Link
                    :href="registrationInterestsIndex().url"
                    class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                >
                    Back
                </Link>
            </div>

            <div class="space-y-6">
                <RegistrationInterestBasicDetails :interest="interest" />
                <RegistrationInterestMessageDetails :interest="interest" />
                <RegistrationInterestAuditDetails :interest="interest" />
            </div>
        </div>
    </div>
</template>
