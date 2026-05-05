<script setup lang="ts">
import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboardCrsd } from '@/routes';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard CRSD',
        href: dashboardCrsd().url,
    },
];


const url = 'https://app.powerbi.com/view?r=eyJrIjoiZDljZWZmYzEtMzk4YS00Mzc3LThiM2EtMzFjN2ZkOTMyYTM4IiwidCI6IjQ1YWIyZTYwLTI4YjktNGE0Zi1hNWQwLWQzYzIxNzU3NGU4NyIsImMiOjEwfQ%3D%3D';
</script>

<template>
    <Head title="Dashboard CRSD" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-[calc(100vh-8rem)] flex-1 flex-col gap-4 overflow-hidden rounded-xl p-4"
        >
            <iframe
                loading="lazy"
                title="Dashboard CRSD"
                class="h-full w-full rounded-lg border-0"
                :src="url"
                sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-forms allow-popups"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
