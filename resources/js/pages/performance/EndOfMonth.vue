<script setup lang="ts">
import { Area, BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import EOMFilter from '@/components/EOMFilter.vue';
import EOMTable from '@/components/EOMTable.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'EOM',
        href: '#',
    },
];

const props = defineProps<{
    areas: Area[];
    users: { id: number; name: string }[]
    categories: any;
    filters: any;
    metadata: any;
    nasional?: {
        total_prognosa: number;
        total_branches: number;
    };
}>();
</script>

<template>
    <Head title="EOM" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <EOMFilter
                :users="props.users"
                :initial-filters="filters"
            />


            <EOMTable
                :categories="categories"
                :performance_etapes="areas"
                :users="props.users"
                :nasional="nasional"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
