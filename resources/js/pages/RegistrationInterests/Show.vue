<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { RegistrationInterest } from '@/types';
import RegistrationInterestAuditDetails from './components/RegistrationInterestAuditDetails.vue';
import RegistrationInterestBasicDetails from './components/RegistrationInterestBasicDetails.vue';
import RegistrationInterestMessageDetails from './components/RegistrationInterestMessageDetails.vue';

defineProps<{
    interest: RegistrationInterest;
}>();
</script>

<template>
    <Head title="Registration interest" />

    <div class="p-6">
        <h1 class="mb-6 text-xl font-semibold text-gray-300">
            Registration interest
        </h1>

        <div class="flex flex-col gap-6">
            <RegistrationInterestBasicDetails :interest="interest" />
            <RegistrationInterestMessageDetails :interest="interest" />
            <RegistrationInterestAuditDetails :interest="interest" />
        </div>
    </div>
</template>
