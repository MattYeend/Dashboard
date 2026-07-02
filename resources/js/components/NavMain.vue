<script setup lang="ts">
import NavMainItem from '@/components/NavMainItem.vue';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <NavMainItem v-for="item in items" :key="item.title" :item="item" />
        </SidebarMenu>
    </SidebarGroup>
</template>
