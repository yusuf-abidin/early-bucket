<script setup lang="ts">
import { Area, BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import etape from '@/routes/etape';
import EtapeFilter from '@/components/EtapeFilter.vue';
import EtapeTable from '@/components/EtapeTable.vue';

const props = defineProps<{
    areas: Area[];
    users: { id: number; name: string }[];
    categories: any;
    filters: any;
    metadata: any;
    nasional?: {
        total_prognosa: number;
        total_branches: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'ETAPE',
        href: etape.index().url,
    },
];
</script>

<template>
    <Head title="ETAPE" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <EtapeFilter
                :users="props.users"
                :initial-filters="filters"
            />

            <EtapeTable
                :performance_etapes="areas"
                :categories="categories"
                :users="props.users"
                :nasional="nasional"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
