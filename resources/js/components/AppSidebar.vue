<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    ClipboardList,
    Contact2,
    CreditCard,
    FolderGit2,
    KeyRound,
    LayoutGrid,
    MapPin,
    Newspaper,
    User2,
    UserPlus,
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
import { index as addressesIndex } from '@/routes/addresses';
import { index as apiTokensIndex } from '@/routes/api-tokens';
import { index as categoriesIndex } from '@/routes/categories';
import { index as companiesIndex } from '@/routes/companies';
import { index as contactsIndex } from '@/routes/contacts';
import { index as industriesIndex } from '@/routes/industries';
import { index as orderStatusesIndex } from '@/routes/order-statuses';
import { index as ordersIndex } from '@/routes/orders';
import { index as plansIndex } from '@/routes/plans';
import { index as postsIndex } from '@/routes/posts';
import { index as tagsIndex } from '@/routes/tags';
import { index as taskStatusesIndex } from '@/routes/task-statuses';
import { index as tasksIndex } from '@/routes/tasks';
import { index as registrationInterestsIndex } from '@/routes/registration-interests';
import { index as usersIndex } from '@/routes/users';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Contacts',
        href: contactsIndex.url(),
        icon: Contact2,
    },
    {
        title: 'Addresses',
        href: addressesIndex.url(),
        icon: MapPin,
    },
    {
        title: 'Companies',
        href: '#',
        icon: FolderGit2,
        children: [
            {
                title: 'All Companies',
                href: companiesIndex.url(),
            },
            {
                title: 'Industries',
                href: industriesIndex.url(),
            },
        ],
    },
    {
        title: 'Plans',
        href: plansIndex.url(),
        icon: CreditCard,
    },
    {
        title: 'Orders',
        href: '#',
        icon: ClipboardList,
        children: [
            {
                title: 'All Orders',
                href: ordersIndex.url(),
            },
            {
                title: 'Order Statuses',
                href: orderStatusesIndex.url(),
            },
        ],
    },
    {
        title: 'Tasks',
        href: '#',
        icon: ClipboardList,
        children: [
            {
                title: 'All Tasks',
                href: tasksIndex.url(),
            },
            {
                title: 'Task Statuses',
                href: taskStatusesIndex.url(),
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
                href: postsIndex.url(),
            },
            {
                title: 'Categories',
                href: categoriesIndex.url(),
            },
            {
                title: 'Tags',
                href: tagsIndex.url(),
            },
        ],
    },
    {
        title: 'Users',
        href: usersIndex.url(),
        icon: User2,
    },
    {
    title: 'Registration Interests',
        href: registrationInterestsIndex.url(),
        icon: UserPlus,
    },
    {
        title: 'API Tokens',
        href: apiTokensIndex.url(),
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
