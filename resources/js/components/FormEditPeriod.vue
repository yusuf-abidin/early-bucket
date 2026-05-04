<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { router, useForm } from '@inertiajs/vue3';
import PerformancePeriodController from '@/actions/App/Http/Controllers/PerformancePeriodController';
import { PerformancePeriod } from '@/types';

const editPeriod = defineModel<PerformancePeriod | null>('edit-period', { default: null });

defineProps<{
    months: {
        value: number;
        label: string;
    }[]
}>()

const isOpen = ref(false);

const form = useForm({
    id: null as number | null,
    start_date: null as number | null,
    end_date: null as number | null,
    year: null as number | null,
    month: null as number | null,
    performance_type: '',
});

watch(
    editPeriod,
    (newValue) => {
        if (newValue) {
            isOpen.value = true;
            const data = {
                id: newValue.id ?? null,
                start_date: newValue.start_date ?? null,
                end_date: newValue.end_date ?? null,
                month: newValue.month ?? null,
                performance_type: newValue.performance_type ?? null,
                year: newValue.year ?? null,
            };
            form.defaults(data);
            form.reset();
        } else {
            isOpen.value = false;
            form.reset();
        }
    },
    { deep: true },
);

const submit = () => {
    const options = {
        onSuccess: () => {
            closeModal();
        },
        perserveScroll: true,
    };

    const route = PerformancePeriodController.upsert.form();
    form.submit(route.method, route.action, options);
};

const closeModal = () => {
    editPeriod.value = null;
    form.reset();
    form.clearErrors();
};

const periodeType = computed(() => {
    if (!editPeriod.value) return null;
    return editPeriod.value.performance_type
        .replace('_', ' ')
        .replace('etape', 'Etape')
        .replace('eom', 'EOM');
});

const deleteDate = () => {
    if (!editPeriod.value?.id) return;
    router.post(
        PerformancePeriodController.deleteDate(editPeriod.value.id),
        {},
        {
            onSuccess: () => {
                isOpen.value = false;
                form.reset();
                editPeriod.value = null;
            },
        },
    );
};
</script>

<template>
    <Dialog v-model:open="isOpen" @update:open="closeModal">
        <DialogContent
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-hidden p-0 sm:max-w-2xl lg:max-w-xl"
        >
            <ScrollArea class="max-h-[calc(100vh-4rem)]">
                <DialogHeader class="px-6 pt-6 pb-4">
                    <DialogTitle> Atur Periode </DialogTitle>
                    <DialogDescription
                        >Tetapkan periode {{ periodeType }} - bulan
                        {{
                            months.find(
                                (m) => m.value === Number(editPeriod?.month),
                            )?.label ?? editPeriod?.month
                        }}</DialogDescription
                    >
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-6 px-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <Label for="start_date"> Tanggal Mulai </Label>
                            <Select
                                v-model="form.start_date"
                                :disabled="form.processing"
                            >
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Pilih Tanggal Mulai"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="date in 31"
                                        :key="date"
                                        :value="date"
                                    >{{ date }}</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="end_date"> Tanggal Selesai </Label>
                            <Select
                                v-model="form.end_date"
                                :disabled="form.processing"
                            >
                                <SelectTrigger>
                                    <SelectValue
                                        placeholder="Pilih Tanggal Selesai"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="date in 31"
                                        :key="date"
                                        :value="date"
                                    >{{ date }}</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                </form>
                <DialogFooter
                    class="flex flex-col-reverse gap-3 px-6 pt-4 pb-6 sm:flex-row sm:items-center sm:justify-between"
                >
                    <Button
                        v-if="editPeriod?.id && (editPeriod?.start_date || editPeriod?.end_date)"
                        variant="destructive"
                        class="w-full gap-1.5 sm:w-auto"
                        @click="deleteDate"
                    >
                        Hapus Tanggal
                    </Button>
                    <div class="flex flex-col gap-2 sm:ml-auto sm:flex-row">
                        <DialogClose asChild>
                            <Button
                                type="button"
                                variant="outline"
                                @click="closeModal"
                                class="w-full sm:w-auto"
                            >
                                Batal
                            </Button>
                        </DialogClose>

                        <Button
                            type="submit"
                            @click="submit"
                            class="w-full min-w-24 gap-1.5 sm:w-auto"
                        >
                            {{ form.processing ? 'Menyimpan' : 'Simpan' }}
                        </Button>
                    </div>
                </DialogFooter>
            </ScrollArea>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
