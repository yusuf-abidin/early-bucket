<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogClose,
} from '@/components/ui/dialog';
import { ref, watch } from 'vue';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { monthList } from '@/lib/utils';
import { Button } from '@/components/ui/button';

const exportImage = defineModel<{
    startMonth: number;
    endMonth: number;
    isExporting: boolean;
} | null>('export-image', { default: null });

const currentDate = new Date();
const currentMonth = currentDate.getMonth() + 1;
const twoMonthsAgo = ((currentMonth - 2 - 1 + 12) % 12) + 1;

const isOpen = ref(false);
const startMonth = ref<number>(twoMonthsAgo);
const endMonth = ref<number>(currentMonth);
const isExporting = ref(false);

watch(
    () => exportImage.value,
    (newVal) => {
        if (newVal) {
            isOpen.value = true;
        }
    },
    { deep: true },
);

const emit = defineEmits<{
    export: [config: { startMonth: number; endMonth: number }];
}>();

const closeModal = () => {
    exportImage.value = null;
    isOpen.value = false;
};

const handleExport = () => {
    if (startMonth.value > endMonth.value) {
        alert('Bulan awal tidak boleh lebih besar dari bulan akhir');
        return;
    }

    try {
        emit('export', {
            startMonth: startMonth.value,
            endMonth: endMonth.value,
        });

        setTimeout(() => {
            closeModal();
            isExporting.value = false;
        }, 500);
    } catch (error) {
        console.log('Export error', error);
        isExporting.value = false;
    }
};
</script>

<template>
    <Dialog v-model:open="isOpen" @update:open="closeModal">
        <DialogContent
            :aria-describedby="undefined"
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-hidden p-0 sm:max-w-2xl lg:max-w-xl"
        >
            <DialogHeader class="px-6 pt-5 pb-2">
                <DialogTitle>Simpan Gambar</DialogTitle>
            </DialogHeader>

            <div class="space-y-4 px-6">
                <div class="space-y-2">
                    <Label for="start_month"> Bulan Awal </Label>
                    <Select v-model="startMonth">
                        <SelectTrigger id="start_month">
                            <SelectValue placeholder="Pilih Awal Bulan" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="month in monthList"
                                :key="month.value"
                                :value="month.value"
                                >{{ month.label }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-2">
                    <Label for="end_month"> Bulan Akhir </Label>
                    <Select v-model="endMonth">
                        <SelectTrigger id="end_month">
                            <SelectValue placeholder="Pilih Akhir Bulan" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="month in monthList"
                                :key="month.value"
                                :value="month.value"
                                >{{ month.label }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <DialogFooter class="px-6 pt-2 pb-4">
                <DialogClose as-child>
                    <Button
                        type="button"
                        variant="outline"
                        @click="closeModal"
                        class="cursor-pointer"
                    >
                        Batal
                    </Button>
                </DialogClose>

                <Button @click="handleExport" class="cursor-pointer">
                    Simpan Gambar
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
