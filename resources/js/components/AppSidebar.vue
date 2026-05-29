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
import {
    ShieldIcon,
    MapPin,
    ChartNoAxesCombined,
    Users,
    BriefcaseBusiness,
    Rocket,
    FolderKanban,
} from 'lucide-vue-next';
import NavUser from '@/components/NavUser.vue';
import { Link, usePage } from '@inertiajs/vue3';
import admin from '@/routes/admin';
import tasks from '@/routes/tasks';
import memos from '@/routes/memos';
import { LayoutDashboard } from 'lucide-vue-next';
import contactCluster from '@/routes/contact-cluster';
import performanceLog from '@/routes/performance-log';
import consumerRecap from '@/routes/consumer-recap';
import branchContact from '@/routes/branch-contact';
import eom from '@/routes/eom';
import etape from '@/routes/etape';
import debtorSavings from '@/routes/debtor-savings';

const props = withDefaults(defineProps<SidebarProps>(), {
    collapsible: 'icon',
});

const page = usePage();
const user = page.props.auth.user;

const mainNavItems: NavItem[] = [
    {
        title: 'Pekerjaan',
        href: dashboard(),
        icon: BriefcaseBusiness,
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
        title: 'Kontak',
        href: '#',
        icon: Users,
        isActive: true,
        items: [
            {
                title: 'Kontak Cluster',
                href: contactCluster.index().url,
            },
            {
                title: 'Kontak BM',
                href: branchContact.index().url,
            },
        ],
    },
    {
        title: 'Rekap',
        href: '#',
        icon: ChartNoAxesCombined,
        isActive: true,
        items: [
            {
                title: 'Pencapaian',
                href: `${performanceLog.index().url}?year=${new Date().getFullYear()}`,
            },
            {
                title: 'Rekapitulasi Konsumer',
                href: `${consumerRecap.index().url}?year=${new Date().getFullYear()}`,
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
                href: eom.index().url,
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
                href: debtorSavings.index().url,
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
            <SidebarMenu v-if="user.role === 'admin'">
                <SidebarMenuItem class="mx-2">
                    <SidebarMenuButton
                        as-child
                        :is-active="$page.url === '/rlqh/tasks'"
                        tooltip="Aplikasi RLQH"
                    >
                        <Link href="/rlqh/tasks">
                            <MapPin class="size-5" />
                            <span>Aplikasi RLQH</span>
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
