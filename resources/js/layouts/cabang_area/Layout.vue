<script setup lang="ts">
import { NavItem } from '@/types';
import admin from '@/routes/admin';
import Heading from '@/components/Heading.vue';
import Button from '@/components/ui/button/Button.vue';
import { toUrl, urlIsActive } from '@/lib/utils';
import { Link } from '@inertiajs/vue3';
import Separator from '@/components/ui/separator/Separator.vue';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Area',
        href: admin.areas.index().url,
    },
    {
        title: 'Cabang',
        href: '#',
    },
];

const currentPath = typeof window !== undefined ? window.location.pathname : '';
</script>

<template>
    <div class="px-4 py-6">
        <Heading
            title="Manajemen Cabang & Area"
            description="Kelola data cabang dan area"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-8">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start',
                            { 'bg-muted': urlIsActive(item.href, currentPath) },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1">
                <section class="space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
