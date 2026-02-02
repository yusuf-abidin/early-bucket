<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarProps,
    SidebarRail,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { AppWindow, FolderKanban, Rocket, ShieldIcon } from 'lucide-vue-next';
import NavUser from '@/components/NavUser.vue';
import { Link, usePage } from '@inertiajs/vue3';
import admin from '@/routes/admin';
import tasks from '@/routes/tasks';
import memos from '@/routes/memos';
import { LayoutDashboard } from 'lucide-vue-next';
import etape from '@/routes/etape';

const props = withDefaults(defineProps<SidebarProps>(), {
    collapsible: 'icon',
});

const page = usePage();
const user = page.props.auth.user;

const mainNavItems: NavItem[] = [
    {
        title: 'App',
        href: dashboard(),
        icon: AppWindow,
        isActive: true,
        items: [
            {
                title: 'Pending Matter',
                href: tasks.index(),
            },
            {
                title: 'History Pekerjaan',
                href: tasks.history(),
            },
            {
                title: 'Pending Memo',
                href: memos.index().url,
            },
            {
                title: 'Arsip Memo',
                href: memos.archive().url,
            },
        ],
    },
    {
        title: 'Performance',
        href: '#',
        icon: Rocket,
        isActive: true,
        items: [
            {
                title: 'ETAPE',
                href: etape.index().url,
            },
            {
                title: 'EOM',
                href: '#',
            },
        ],
    },
    {
        title: 'Project',
        href: '#',
        icon: FolderKanban,
        isActive: true,
        items: [
            {
                title: 'Debitur Menabung',
                href: '#'
            },
        ]
    },
];

const adminNavItem: NavItem[] = [
    {
        title: 'Admin',
        href: '#',
        icon: ShieldIcon,
        isActive: true,
        items: [
            {
                title: 'Users',
                href: admin.users.index().url,
            },
            {
                title: 'Area/Cabang',
                href: admin.areas.index().url,
            },
            {
                title: 'Manajemen kategori',
                href: admin.categories.index().url,
            },
        ],
    },
];
</script>

<template>
    <Sidebar v-bind="props">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <div
                                class="flex aspect-square size-8 items-center justify-center rounded-lg text-sidebar-primary-foreground"
                            >
                                <img
                                    src="/favicon-btn.svg"
                                    alt="Icon BTN"
                                    class="size-8"
                                />
                            </div>
                            <div
                                class="grid flex-1 text-left text-sm leading-tight"
                            >
                                <span class="truncate font-medium"
                                    >Early Bucket</span
                                >
                                <span class="truncate text-xs">CRSD1</span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
            <SidebarMenu>
                <SidebarMenuItem class="mx-2">
                    <SidebarMenuButton
                        as-child
                        :is-active="$page.url === '/dashboard'"
                        tooltip="Dashboard"
                    >
                        <Link href="/dashboard">
                            <LayoutDashboard class="size-5" />
                            <span>Dashboard</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <NavMain :items="mainNavItems" label="App" />
            <NavMain
                v-if="user.role === 'admin'"
                :items="adminNavItem"
                label="Admin"
            />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>
    <!--    <Sidebar collapsible="icon" variant="inset">-->
    <!--        <SidebarHeader>-->
    <!--            <SidebarMenu>-->
    <!--                <SidebarMenuItem>-->
    <!--                    <SidebarMenuButton size="lg" as-child>-->
    <!--                        <Link :href="dashboard()">-->
    <!--                            <AppLogo />-->
    <!--                        </Link>-->
    <!--                    </SidebarMenuButton>-->
    <!--                </SidebarMenuItem>-->
    <!--            </SidebarMenu>-->
    <!--        </SidebarHeader>-->

    <!--        <SidebarContent>-->
    <!--    <NavMain :items="mainNavItems" />-->
    <!--        </SidebarContent>-->

    <!--        <SidebarFooter>-->
    <!--            <NavFooter :items="footerNavItems" />-->
    <!--            <NavUser />-->
    <!--        </SidebarFooter>-->
    <!--    </Sidebar>-->
    <slot />
</template>
