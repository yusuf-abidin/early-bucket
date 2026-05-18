<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import RekapitulasiYear from '@/components/RekapitulasiYear.vue';
import ConsumerRecapTable from '@/components/ConsumerRecapTable.vue';
import { Button } from '@/components/ui/button';
import ConsumerRecapExport from '@/pages/performance/ConsumerRecapExport.vue';
import { ref } from 'vue';
import DialogExportConsumerRecap from '@/components/DialogExportConsumerRecap.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Rekap Konsumer',
        href: '#',
    },
];

defineProps<{
    consumer_recaps: any;
    selected_year: number;
}>();

const exportCanvasRef = ref<InstanceType<typeof ConsumerRecapExport>>();

const exportImage = ref<{
    startMonth: number;
    endMonth: number;
    isExporting: boolean;
}>();

const openExportDialog = () => {
    exportImage.value = {
        startMonth: 1,
        endMonth: 12,
        isExporting: true,
    };
};

const handleExportImage = async (config: {
    startMonth: number;
    endMonth: number;
}) => {
    // Panggil component export canvas
    await exportCanvasRef.value?.exportToImage(
        config.startMonth,
        config.endMonth,
    );
};
</script>

<template>
    <Head title="Rekap Konsumer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex space-x-4">
                <RekapitulasiYear />
                <Button variant="outline" @click="openExportDialog" class="cursor-pointer">
                    Simpan Gambar
                </Button>
            </div>

            <ConsumerRecapTable
                :consumer_recaps="consumer_recaps"
                :selected_year="selected_year"
            />
            <div class="pointer-events-none absolute -left-[99999px] top-0 opacity-0">
                <ConsumerRecapExport
                    ref="exportCanvasRef"
                    :consumer_recaps="consumer_recaps"
                    :selected_year="selected_year"
                />
            </div>


            <DialogExportConsumerRecap
                @export="handleExportImage"
                v-model:export-image="exportImage"
            />
        </div>
    </AppLayout>
</template>

<style scoped></style>
