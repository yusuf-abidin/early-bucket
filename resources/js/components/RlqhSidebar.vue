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
import { AppWindow, LayoutDashboard, ShieldIcon } from 'lucide-vue-next';
import NavUser from '@/components/NavUser.vue';
import { Link, usePage } from '@inertiajs/vue3';
import admin from '@/routes/admin';
import rlqh from '@/routes/rlqh';

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
                href: rlqh.tasks.index().url,
            },
            {
                title: 'History Pekerjaan',
                href: rlqh.tasks.history().url,
            },
        ],
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
                title: 'Pengguna',
                href: admin.rlqh.users.index().url,
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
                        <Link href="#">
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
            <SidebarMenu v-if="user.role === 'admin'">
                <SidebarMenuItem class="mx-2">
                    <SidebarMenuButton
                        as-child
                        :is-active="$page.url === '/dashboard'"
                        tooltip="Early Bucket"
                    >
                        <Link href="/dashboard">
                            <LayoutDashboard class="size-5" />
                            <span>Early Bucket</span>
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
    <slot />
</template>
