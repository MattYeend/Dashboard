<script setup lang="ts">
import ImpersonationBanner from '@/components/ImpersonationBanner.vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <ImpersonationBanner />
        <slot />
    </AppLayout>
</template>
