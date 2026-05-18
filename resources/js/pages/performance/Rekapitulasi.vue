<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, PerformancePeriod, Regional } from '@/types';
import performanceLog from '@/routes/performance-log';
import RekapitulasiYear from '@/components/RekapitulasiYear.vue';
import RekapitulasiTable from '@/components/RekapitulasiTable.vue';
import { ref } from 'vue';
import FormEditPeriod from '@/components/FormEditPeriod.vue';
import FormListEtape from '@/components/FormListEtape.vue';

const months = [
    { value: 1, label: 'Januari' },
    { value: 2, label: 'Februari' },
    { value: 3, label: 'Maret' },
    { value: 4, label: 'April' },
    { value: 5, label: 'Mei' },
    { value: 6, label: 'Juni' },
    { value: 7, label: 'Juli' },
    { value: 8, label: 'Agustus' },
    { value: 9, label: 'September' },
    { value: 10, label: 'Oktober' },
    { value: 11, label: 'November' },
    { value: 12, label: 'Desember' },
];

const props = defineProps<{
    year: number;
    periods: Record<number, Record<string, PerformancePeriod>>;
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
    totals: {
        regional: Record<number, {
            etape: number;
            eom: number;
        }>;
        area: Record<number, {
            etape: number;
            eom: number;
        }>;
        branch: Record<number, {
            etape: number;
            eom: number;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pencapaian',
        href: performanceLog.index().url,
    },
];

const editPeriod = ref<PerformancePeriod | null>(null);

const selectedMonth = ref<{
    year: number;
    month: number;
    availableType: PerformancePeriod[];
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
                v-model:selected-month="selectedMonth"
                :months="months"
                :totals="props.totals"
            />

            <FormEditPeriod v-model:edit-period="editPeriod" :months="months" />

            <FormListEtape
                v-model:selected-month="selectedMonth"
                :months="months"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
