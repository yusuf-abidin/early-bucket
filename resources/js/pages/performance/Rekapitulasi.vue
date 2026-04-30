<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, Regional } from '@/types';
import performanceLog from '@/routes/performance-log';
import RekapitulasiYear from '@/components/RekapitulasiYear.vue';
import RekapitulasiTable from '@/components/RekapitulasiTable.vue';
import { ref } from 'vue';
import FormEditPeriod from '@/components/FormEditPeriod.vue';

const props = defineProps<{
    year: number;
    periods: Record<
        number,
        Record<
            string,
            {
                id: number | null;
                month: number;
                type: string;
                start_date: number | null;
                end_date: number | null;
            }
        >
    >;
    log_index: Record<
        number,
        Record<
            string,
            Record<
                number,
                {
                    id: number;
                    is_achieved: boolean;
                }
            >
        >
    >;
    regionals: Regional[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Rekapitulasi',
        href: performanceLog.index().url,
    },
];

const editPeriod = ref<{
    id: number | null;
    month: number;
    type: string;
    start_date: number | null;
    end_date: number | null;
    year: number
} | null>(null);
</script>

<template>
    <Head title="Rekapitulasi" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <RekapitulasiYear />

            <RekapitulasiTable
                :year="props.year"
                :log_index="props.log_index"
                :periods="props.periods"
                :regionals="props.regionals"
                v-model:edit-period="editPeriod"
            />

            <FormEditPeriod
                v-model:edit-period="editPeriod"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
