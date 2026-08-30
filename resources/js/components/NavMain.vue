<script setup lang="ts">
import { ChevronRight } from 'lucide-vue-next';
import NavMainItem from '@/components/NavMainItem.vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
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

    <Collapsible
        v-for="group in groups"
        :key="group.title"
        as-child
        class="group/collapsible"
    >
        <SidebarGroup class="px-2 py-0">
            <SidebarGroupLabel as-child>
                <CollapsibleTrigger class="w-full cursor-pointer">
                    {{ group.title }}
                    <ChevronRight
                        class="ml-auto size-4 shrink-0 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                    />
                </CollapsibleTrigger>
            </SidebarGroupLabel>
            <CollapsibleContent>
                <SidebarMenu>
                    <NavMainItem
                        v-for="item in group.items"
                        :key="item.title"
                        :item="item"
                    />
                </SidebarMenu>
            </CollapsibleContent>
        </SidebarGroup>
    </Collapsible>
</template>
