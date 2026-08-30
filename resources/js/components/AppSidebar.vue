<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Archive,
    CalendarDays,
    ClipboardList,
    Contact2,
    CreditCard,
    FolderGit2,
    MessageSquareWarning,
    HandCoins,
    KeyRound,
    LayoutGrid,
    MapPin,
    Megaphone,
    Newspaper,
    Receipt,
    Settings,
    SquareCheckBig,
    Tag,
    Ticket,
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
import { index as backupsIndex } from '@/routes/backups';
import { index as calendarIndex } from '@/routes/calendar';
import { board as dealsBoard } from '@/routes/deals';
import { index as notificationBroadcastsIndex } from '@/routes/notification-broadcasts';
import { index as systemIndex } from '@/routes/system';
import type { NavGroup, NavItem } from '@/types';

const pinnedNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const navGroups: NavGroup[] = [
    {
        title: 'CRM',
        items: [
            {
                title: 'Contacts',
                href: '/contacts',
                icon: Contact2,
            },
            {
                title: 'Companies',
                href: '/companies',
                icon: FolderGit2,
                children: [
                    { title: 'All Companies', href: '/companies' },
                    { title: 'Industries', href: '/industries' },
                ],
            },
            {
                title: 'Addresses',
                href: '/addresses',
                icon: MapPin,
            },
        ],
    },
    {
        title: 'Sales',
        items: [
            {
                title: 'Pipelines',
                href: '/pipelines',
                icon: Workflow,
                children: [
                    { title: 'All Pipelines', href: '/pipelines' },
                    { title: 'Pipeline Statuses', href: '/pipeline-statuses' },
                ],
            },
            {
                title: 'Deals',
                href: '/deals',
                icon: HandCoins,
                children: [
                    { title: 'All Deals', href: '/deals' },
                    { title: 'Board', href: dealsBoard() },
                    { title: 'Deal Statuses', href: '/deal-statuses' },
                ],
            },
            {
                title: 'Invoices',
                href: '/invoices',
                icon: Receipt,
                children: [
                    { title: 'All Invoices', href: '/invoices' },
                    { title: 'Invoice Statuses', href: '/invoice-statuses' },
                ],
            },
            {
                title: 'Orders',
                href: '/orders',
                icon: ClipboardList,
                children: [
                    { title: 'All Orders', href: '/orders' },
                    { title: 'Order Statuses', href: '/order-statuses' },
                ],
            },
        ],
    },
    {
        title: 'Operations',
        items: [
            {
                title: 'Calendar',
                href: calendarIndex(),
                icon: CalendarDays,
            },
            {
                title: 'Tasks',
                href: '/tasks',
                icon: SquareCheckBig,
                children: [
                    { title: 'All Tasks', href: '/tasks' },
                    { title: 'Task Statuses', href: '/task-statuses' },
                ],
            },
            {
                title: 'Posts',
                href: '/posts',
                icon: Newspaper,
                children: [
                    { title: 'All Posts', href: '/posts' },
                    { title: 'Categories', href: '/categories' },
                    { title: 'Tags', href: '/tags' },
                ],
            },
            {
                title: 'Tickets',
                href: '/tickets',
                icon: Ticket,
                children: [
                    { title: 'All Tickets', href: '/tickets' },
                    { title: 'Ticket Statuses', href: '/ticket-statuses' },
                    { title: 'Ticket Priorities', href: '/ticket-priorities' },
                ],
            },
            {
                title: 'Labels',
                href: '/labels',
                icon: Tag,
            },
        ],
    },
    {
        title: 'Administration',
        items: [
            {
                title: 'Users',
                href: '/users',
                icon: User2,
            },
            {
                title: 'Notification Broadcasts',
                href: notificationBroadcastsIndex(),
                icon: Megaphone,
            },
            {
                title: 'API Tokens',
                href: '/api-tokens',
                icon: KeyRound,
            },
            {
                title: 'Plans',
                href: '/plans',
                icon: CreditCard,
            },
            {
                title: 'Registration Interests',
                href: '/registration-interests',
                icon: UserPlus,
            },
            {
                title: 'System',
                href: systemIndex(),
                icon: Settings,
            },
            {
                title: 'Backups',
                href: backupsIndex(),
                icon: Archive,
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/MattYeend/Dashboard',
        icon: FolderGit2,
    },
    {
        title: 'Report an Issue',
        href: 'https://github.com/MattYeend/Dashboard/issues',
        icon: MessageSquareWarning,
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
            <NavMain :items="pinnedNavItems" :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
