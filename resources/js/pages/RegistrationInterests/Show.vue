<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { index as registrationInterestsIndex } from '@/routes/registration-interests';
import type { PermissionsMeta, RegistrationInterest } from '@/types';
import RegistrationInterestAuditDetails from './components/RegistrationInterestAuditDetails.vue';
import RegistrationInterestBasicDetails from './components/RegistrationInterestBasicDetails.vue';
import RegistrationInterestMessageDetails from './components/RegistrationInterestMessageDetails.vue';

defineProps<{
    interest: RegistrationInterest;
    permissions_meta: PermissionsMeta;
}>();
</script>

<template>
    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b pb-4"
            >
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ interest.name }}
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
