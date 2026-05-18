<script setup lang="ts">
import { monthGradients, monthList } from '@/lib/utils';
import html2canvas from 'html2canvas-pro';
import { computed, ref } from 'vue';

const props = defineProps<{
    consumer_recaps: any;
    selected_year: number;
}>();

const startMonth = ref<number>(1);
const endMonth = ref<number>(12);
const isExporting = ref(false);

const getFilteredRecaps = (start: number, end: number) => {
    return props.consumer_recaps.filter((_: any, index: number) => {
        const month = index + 1;
        return month >= start && month <= end;
    });
};

const filteredRecaps = computed(() => {
    return getFilteredRecaps(startMonth.value, endMonth.value);
});

const getFileName = (start: number, end: number) => {
    const startLabel = monthList.find((m) => m.value === start)?.label || 'Jan';
    const endLabel = monthList.find((m) => m.value === end)?.label || 'Des';
    return `Rekap-Konsumen-${startLabel}-${endLabel}-${props.selected_year}.png`;
};

async function exportToImage(startMonthParam?: number, endMonthParam?: number) {
    const startVal = startMonthParam ?? startMonth.value;
    const endVal = endMonthParam ?? endMonth.value;
    const recapsToExport = getFilteredRecaps(startVal, endVal);

    if (!recapsToExport.length) {
        alert('Pilih minimal 1 bulan');
        return;
    }

    try {
        isExporting.value = true;

        // Update ref agar DOM di-render dengan bulan yang benar
        startMonth.value = startVal;
        endMonth.value = endVal;

        // Wait untuk DOM ter-update
        await new Promise((resolve) => setTimeout(resolve, 100));

        const element = document.getElementById('export-table-container');
        if (!element) {
            alert('Tidak ada data untuk di-export');
            isExporting.value = false;
            return;
        }

        const canvas = await html2canvas(element, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
            imageTimeout: 0,
        });

        canvas.toBlob(
            (blob) => {
                if (!blob) return;

                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = getFileName(startVal, endVal);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);

                isExporting.value = false;
            },
            'image/png',
            1,
        );
    } catch (error) {
        console.error('Error exporting to image:', error);
        alert('Gagal mengekspor gambar');
        isExporting.value = false;
    }
}

function getMonthBackground(monthNum: number): string {
    return (
        monthGradients[monthNum] || 'bg-linear-to-r from-gray-500 to-slate-500'
    );
}

const formatRibuan = (value: number) => {
    if (value === null || value === undefined || String(value) === '') {
        return '';
    }
    return new Intl.NumberFormat('id-ID').format(value);
};

const gridClass = computed(() => {
    const count = filteredRecaps.value.length;
    if (count <= 6) {
        return 'flex flex-wrap justify-center gap-4';
    }
    return 'grid grid-cols-[repeat(6,320px)] gap-4 justify-center';
});

defineExpose({
    exportToImage,
});
</script>

<template>
    <div
        id="export-table-container"
        class="space-y-2 rounded-lg border border-gray-200 bg-white p-2"
    >
        <!-- Header Info -->
        <div class="border-b border-gray-200 pb-1 text-center">
            <h2 class="text-lg font-bold text-gray-800">
                REKAP KONSUMER - {{ selected_year }}
            </h2>
            <p class="text-sm text-gray-600">
                {{ monthList.find((m) => m.value === startMonth)?.label }} -
                {{ monthList.find((m) => m.value === endMonth)?.label }}
            </p>
        </div>

        <!-- Tables by Month -->
        <div :class="['mx-auto w-max', gridClass]">
            <div
                v-for="(month, monthIndex) in filteredRecaps"
                :key="monthIndex"
                class="w-80 overflow-hidden rounded-xl border border-gray-200 shadow-sm"
            >
                <div class="grid grid-cols-3 text-center">
                    <!-- Header bulan -->
                    <div
                        :class="getMonthBackground(month.value)"
                        class="col-span-3 py-2 text-lg font-bold text-white"
                    >
                        {{
                            monthList.find((m) => m.value === month.value)
                                ?.label
                        }}
                        {{ selected_year }}
                    </div>

                    <!-- Column headers -->
                    <div
                        class="border-r border-b border-gray-200 py-1 text-xs font-bold"
                    >
                        Tgl
                    </div>
                    <div
                        class="border-r border-b border-gray-200 py-1 text-xs font-bold"
                    >
                        Cons
                    </div>
                    <div
                        class="border-b border-gray-200 py-1 text-xs font-bold"
                    >
                        %
                    </div>

                    <!-- Rows -->
                    <template v-for="day in month.days" :key="day.date">
                        <div
                            class="border-r border-b border-gray-100 p-1 text-xs"
                        >
                            {{ day.date }}
                        </div>
                        <div
                            class="border-r border-b border-gray-100 p-1 text-xs"
                        >
                            {{ formatRibuan(day.consumer) }}
                        </div>
                        <div class="border-b border-gray-100 p-1 text-xs">
                            {{ day.percent }}
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Footer timestamp -->
        <div class="border-t border-gray-200 pt-2 text-center">
            <p class="text-xs text-gray-500">
                Generated: {{ new Date().toLocaleString('id-ID') }}
            </p>
        </div>
    </div>
</template>

<style scoped>
/* Styling untuk html2canvas compatibility */
* {
    box-sizing: border-box;
}
</style>
