<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

const props = defineProps<{
    item: NavItem;
}>();

const { isCurrentUrl } = useCurrentUrl();

const hasChildren = props.item.children && props.item.children.length > 0;
const isChildActive = hasChildren
    ? props.item.children!.some((child) => isCurrentUrl(child.href))
    : false;
</script>

<template>
    <Collapsible
        v-if="hasChildren"
        as-child
        :default-open="isChildActive"
        class="group/collapsible"
    >
        <SidebarMenuItem>
            <CollapsibleTrigger as-child>
                <SidebarMenuButton
                    :is-active="isChildActive"
                    :tooltip="item.title"
                >
                    <component :is="item.icon" v-if="item.icon" />
                    <span>{{ item.title }}</span>
                    <ChevronRight
                        class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                    />
                </SidebarMenuButton>
            </CollapsibleTrigger>
            <CollapsibleContent>
                <SidebarMenuSub>
                    <SidebarMenuSubItem
                        v-for="child in item.children"
                        :key="child.title"
                    >
                        <SidebarMenuSubButton
                            as-child
                            :is-active="isCurrentUrl(child.href)"
                        >
                            <Link :href="child.href">
                                <span>{{ child.title }}</span>
                            </Link>
                        </SidebarMenuSubButton>
                    </SidebarMenuSubItem>
                </SidebarMenuSub>
            </CollapsibleContent>
        </SidebarMenuItem>
    </Collapsible>

    <SidebarMenuItem v-else>
        <SidebarMenuButton
            as-child
            :is-active="isCurrentUrl(item.href)"
            :tooltip="item.title"
        >
            <Link :href="item.href">
                <component :is="item.icon" v-if="item.icon" />
                <span>{{ item.title }}</span>
            </Link>
        </SidebarMenuButton>
    </SidebarMenuItem>
</template>
