<script setup lang="ts">
import { ref, watch } from 'vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { router } from '@inertiajs/vue3';


const getQueryParam = (key: string): string | null => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(key);
};

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 8 }, (_, i) => currentYear - 2 + i).map(
    (year) => ({
        value: year,
        label: year,
    }),
);

const getInitialYear = (): number => {
    const urlYear = getQueryParam('year');
    return urlYear ? Number(urlYear) : currentYear
}

const selectedYear = ref<number>(getInitialYear());

watch(selectedYear, (newValue) => {
    const query: Record<string, any> = {
        year: newValue
    }
    router.get(window.location.pathname, query, {
        replace: true,
        preserveState: true,
    })
})
</script>

<template>
    <Select v-model="selectedYear">
        <SelectTrigger>
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
</template>

<style scoped></style>
