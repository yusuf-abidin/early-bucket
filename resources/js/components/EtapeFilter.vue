<script setup lang="ts">
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    users: { id: number; name: string }[];
    initialFilters?: {
        userIds?: number[];
        etapeNo: number;
        year: number;
        month: number;
    };
}>();

const getQueryParam = (key: string): string | null => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(key);
};

const getArrayQueryParam = (key: string): number[] => {
    const urlParams = new URLSearchParams(window.location.search);
    const values: number[] = [];

    // Cek format array: user_ids[0], user_ids[1], dst
    let index = 0;
    while (true) {
        const value = urlParams.get(`${key}[${index}]`);
        if (value === null) break;
        values.push(Number(value));
        index++;
    }

    return values;
};

const selectedUsers = ref<number[]>(
    (() => {
        const urlUserIds = getArrayQueryParam('user_ids');
        if (urlUserIds.length > 0) {
            return urlUserIds;
        }
        return props.initialFilters?.userIds || [];
    })(),
);

const applyFilters = () => {
    const query: Record<string, any> = {
        user_ids:
            selectedUsers.value.length > 0 ? selectedUsers.value : undefined,
        etape_no: Number(selectedEtape.value),
        month: Number(selectedMonth.value),
        year: Number(selectedYear.value),
    };

    router.get(window.location.pathname, query, {
        preserveState: true,
        replace: true,
    });
};

const selectEtape = [
    {
        etape_no: 1,
        label: 'Etape 1',
    },
    {
        etape_no: 2,
        label: 'Etape 2',
    },
    {
        etape_no: 3,
        label: 'Etape 3',
    },
];

const selectedEtape = ref<number>(
    (() => {
        const urlEtapeNo = getQueryParam('etape_no');
        if (urlEtapeNo) {
            return Number(urlEtapeNo);
        }
        return props.initialFilters?.etapeNo || 1;
    })(),
);

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

const selectedMonth = ref(
    (() => {
        const urlMonth = getQueryParam('month');
        if (urlMonth) {
            return Number(urlMonth);
        }
        return props.initialFilters?.month || new Date().getMonth() + 1;
    })(),
);
const currentYear = new Date().getFullYear();
const years = Array.from({ length: 8 }, (_, i) => currentYear - 2 + i).map(
    (year) => ({
        value: year,
        label: String(year),
    }),
);

const selectedYear = ref(
    (() => {
        const urlYear = getQueryParam('year');
        if (urlYear) {
            return Number(urlYear);
        }
        return props.initialFilters?.year || new Date().getFullYear();
    })(),
);

watch(
    [selectedUsers, selectedEtape, selectedMonth, selectedYear],
    () => {
        applyFilters();
    },
    { deep: true },
);

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.toString() === '') {
        applyFilters();
    }
});
</script>

<template>
    <div
        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <div class="flex flex-wrap items-center gap-2">
            <Select v-model="selectedEtape">
                <SelectTrigger class="w-full sm:w-[180px]">
                    <SelectValue placeholder="Pilih Etape" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="etape in selectEtape"
                        :key="etape.etape_no"
                        :value="etape.etape_no"
                    >
                        {{ etape.label }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Select v-model="selectedMonth">
                <SelectTrigger class="w-full sm:w-[180px]">
                    <SelectValue placeholder="Pilih Bulan" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="month in months"
                        :key="month.value"
                        :value="month.value"
                    >
                        {{ month.label }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Select v-model="selectedYear">
                <SelectTrigger class="w-full sm:w-[100px]">
                    <SelectValue placeholder="Pilih Tahun" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="year in years"
                        :key="year.value"
                        :value="year.value"
                    >
                        {{ year.label }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Select v-model="selectedUsers" multiple>
                <SelectTrigger class="w-full sm:w-[180px]">
                    <SelectValue placeholder="Filter pengguna" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="user in props.users"
                        :key="user.id"
                        :value="user.id"
                    >
                        {{ user.name }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>
    </div>
</template>

<style scoped></style>
