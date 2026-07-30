<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    ClipboardList,
    Contact2,
    CreditCard,
    FolderGit2,
    HandCoins,
    KeyRound,
    LayoutGrid,
    MapPin,
    Newspaper,
    Receipt,
    SquareCheckBig,
    User2,
    UserPlus,
    Workflow,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Contacts',
        href: '/contacts',
        icon: Contact2,
    },
    {
        title: 'Addresses',
        href: '/addresses',
        icon: MapPin,
    },
    {
        title: 'Companies',
        href: '#',
        icon: FolderGit2,
        children: [
            {
                title: 'All Companies',
                href: '/companies',
            },
            {
                title: 'Industries',
                href: '/industries',
            },
        ],
    },
    {
        title: 'Plans',
        href: '/plans',
        icon: CreditCard,
    },
    {
        title: 'Orders',
        href: '#',
        icon: ClipboardList,
        children: [
            {
                title: 'All Orders',
                href: '/orders',
            },
            {
                title: 'Order Statuses',
                href: '/order-statuses',
            },
        ],
    },
    {
        title: 'Invoices',
        href: '#',
        icon: Receipt,
        children: [
            {
                title: 'All Invoices',
                href: '/invoices',
            },
            {
                title: 'Invoice Statuses',
                href: '/invoice-statuses',
            },
        ],
    },
    {
        title: 'Pipelines',
        href: '#',
        icon: Workflow,
        children: [
            {
                title: 'All Pipelines',
                href: '/pipelines',
            },
            {
                title: 'Pipeline Statuses',
                href: '/pipeline-statuses',
            },
        ],
    },
    {
        title: 'Deals',
        href: '#',
        icon: HandCoins,
        children: [
            {
                title: 'All Deals',
                href: '/deals',
            },
            {
                title: 'Deal Statuses',
                href: '/deal-statuses',
            },
        ],
    },
    {
        title: 'Tasks',
        href: '#',
        icon: SquareCheckBig,
        children: [
            {
                title: 'All Tasks',
                href: '/tasks',
            },
            {
                title: 'Task Statuses',
                href: '/task-statuses',
            },
        ],
    },
    {
        title: 'Posts',
        href: '#',
        icon: Newspaper,
        children: [
            {
                title: 'All Posts',
                href: '/posts',
            },
            {
                title: 'Categories',
                href: '/categories',
            },
            {
                title: 'Tags',
                href: '/tags',
            },
        ],
    },
    {
        title: 'Users',
        href: '/users',
        icon: User2,
    },
    {
        title: 'Registration Interests',
        href: '/registration-interests',
        icon: UserPlus,
    },
    {
        title: 'API Tokens',
        href: '/api-tokens',
        icon: KeyRound,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
