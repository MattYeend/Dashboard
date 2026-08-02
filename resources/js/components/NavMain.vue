<script setup lang="ts">
import NavMainItem from '@/components/NavMainItem.vue';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
} from '@/components/ui/sidebar';
import type { NavGroup, NavItem } from '@/types';

defineProps<{
    items?: NavItem[];
    groups: NavGroup[];
}>();
</script>

<template>
    <SidebarGroup v-if="items?.length" class="px-2 py-0">
        <SidebarMenu>
            <NavMainItem v-for="item in items" :key="item.title" :item="item" />
        </SidebarMenu>
    </SidebarGroup>

    <SidebarGroup v-for="group in groups" :key="group.title" class="px-2 py-0">
        <SidebarGroupLabel>{{ group.title }}</SidebarGroupLabel>
        <SidebarMenu>
            <NavMainItem
                v-for="item in group.items"
                :key="item.title"
                :item="item"
            />
        </SidebarMenu>
    </SidebarGroup>
</template>
