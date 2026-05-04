<script setup lang="ts">
import {
    Dialog, DialogClose, DialogContent,
    DialogFooter, DialogHeader, DialogTitle,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { ref, watch } from 'vue';
import { PerformancePeriod } from '@/types';
import { Trash2, Plus, GripVertical } from 'lucide-vue-next';
import performancePeriod from '@/routes/performance-period';
import { router } from '@inertiajs/vue3';
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/components/ui/select';

const isOpen = ref<boolean>(false);
const localTypes = ref<PerformancePeriod[]>([]);

const dragIndex = ref<number | null>(null);

const overIndex = ref<number | null>(null);

defineProps<{
    months: { value: number; label: string }[];
}>();


const onDragStart = (index: number) => {
    dragIndex.value = index;
};

const onDragOver = (e: DragEvent, index: number) => {
    e.preventDefault();
    overIndex.value = index;
};

const onDragLeave = () => {
    overIndex.value = null;
};

const onDrop = (targetIndex: number) => {
    if (dragIndex.value === null || dragIndex.value === targetIndex) return;

    const items = [...localTypes.value];
    const [moved] = items.splice(dragIndex.value, 1);
    items.splice(targetIndex, 0, moved);
    localTypes.value = items;

    dragIndex.value = null;
    overIndex.value = null;
};

const onDragEnd = () => {
    dragIndex.value = null;
    overIndex.value = null;
};

const submit = () => {
    if (!selectedMonth.value) return;
    const payload = localTypes.value
        .filter((type) => type.display_name?.trim())
        .map(({ display_name, ...type }, index) => ({
            ...type,
            performance_type: denormalizedType(display_name || ''),
            order: index,
        }));

    router.post(
        performancePeriod.bulkUpdate().url,
        {
            year: selectedMonth.value.year,
            month: selectedMonth.value.month,
            list_periods: payload
        },
        { preserveScroll: true, onSuccess: () => closeModal() },
    );
};

const closeModal = () => {
    isOpen.value = false;
    selectedMonth.value = null;
    localTypes.value = [];
};

const selectedMonth = defineModel<{
    year: number;
    month: number;
    availableType: PerformancePeriod[];
} | null>('selected-month', { default: null });

watch(selectedMonth, (newMonth) => {
    if (newMonth) {
        isOpen.value = true;
        localTypes.value = newMonth.availableType
            .slice()
            .sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
            .map((t) => ({
                ...t,
                display_name: normalizedType(t.performance_type),
            }));
    } else {
        isOpen.value = false;
        localTypes.value = [];
    }
});

const addField = () => {
    if (!selectedMonth.value) return;
    localTypes.value.push({
        id: null,
        month: selectedMonth.value.month,
        year: selectedMonth.value.year,
        performance_type: '',
        start_date: null,
        end_date: null,
        order: localTypes.value.length,
    });
};

const removeField = (index: number) => {
    localTypes.value.splice(index, 1);
};

const normalizedType = (value: string) =>
    value.replace(/_/g, ' ')
        .replace(/\betape\b/gi, 'Etape')
        .replace(/\beom\b/gi, 'EOM');

const denormalizedType = (value: string) =>
    value.trim().replace(/\s+/g, '_').toLowerCase();
</script>

<template>
    <Dialog v-model:open="isOpen" @update:open="closeModal">
        <DialogContent
            :aria-describedby="undefined"
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-hidden p-0 sm:max-w-2xl lg:max-w-xl"
        >
            <ScrollArea class="max-h-[calc(100vh-4rem)]">
                <DialogHeader class="px-6 pt-6 pb-4">
                    <DialogTitle>
                        Rekapitulasi
                        {{
                            months.find(
                                (m) => m.value === Number(selectedMonth?.month),
                            )?.label ?? selectedMonth?.month
                        }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-4 px-6">

                    <!-- HEADER LABELS -->
                    <div class="grid grid-cols-[24px_5fr_3fr_3fr_28px] gap-3 text-sm font-medium text-muted-foreground">
                        <div />
                        <Label>Nama</Label>
                        <Label>Tanggal Mulai</Label>
                        <Label>Tanggal Selesai</Label>
                        <div />
                    </div>

                    <!-- DRAGGABLE LIST -->
                    <div class="space-y-2">
                        <div
                            v-for="(type, idx) in localTypes"
                            :key="type.id ?? `new-${idx}`"
                            draggable="true"
                            @dragstart="onDragStart(idx)"
                            @dragover="onDragOver($event, idx)"
                            @dragleave="onDragLeave"
                            @drop="onDrop(idx)"
                            @dragend="onDragEnd"
                            class="grid grid-cols-[24px_5fr_3fr_3fr_28px] items-center gap-3 rounded-md border px-2 py-1 transition-colors"
                            :class="{
                                'opacity-40 border-dashed':        dragIndex === idx,
                                'border-primary bg-accent':        overIndex === idx && dragIndex !== idx,
                                'border-transparent':              overIndex !== idx && dragIndex !== idx,
                            }"
                        >
                            <!-- DRAG HANDLE -->
                            <div class="flex cursor-grab justify-center text-muted-foreground active:cursor-grabbing">
                                <GripVertical class="h-4 w-4" />
                            </div>

                            <!-- NAME -->
                            <Input
                                class="h-9"
                                v-model="localTypes[idx].display_name"
                                :placeholder="normalizedType(type.performance_type) || 'Masukkan nama'"
                                @dragstart.stop
                            />

                            <!-- START DATE -->
                            <Select v-model="localTypes[idx].start_date">
                                <SelectTrigger class="h-9">
                                    <SelectValue placeholder="Mulai" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">-</SelectItem>
                                    <SelectItem v-for="date in 31" :key="date" :value="date">
                                        {{ date }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <!-- END DATE -->
                            <Select v-model="localTypes[idx].end_date">
                                <SelectTrigger class="h-9">
                                    <SelectValue placeholder="Selesai" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">-</SelectItem>
                                    <SelectItem v-for="date in 31" :key="date" :value="date">
                                        {{ date }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <!-- DELETE -->
                            <button
                                type="button"
                                @click="removeField(idx)"
                                class="flex justify-center"
                            >
                                <Trash2 class="h-4 w-4 text-destructive" />
                            </button>
                        </div>
                    </div>

                    <!-- TAMBAH -->
                    <button
                        type="button"
                        @click="addField"
                        class="flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <Plus class="h-4 w-4" />
                        <span>Tambah</span>
                    </button>
                </form>

                <DialogFooter
                    class="flex flex-col-reverse gap-3 border-t px-6 pt-4 pb-6 mt-4
                           sm:flex-row sm:items-center sm:justify-between"
                >
                    <DialogClose asChild>
                        <Button type="button" variant="outline" @click="closeModal" class="w-full sm:w-auto">
                            Batal
                        </Button>
                    </DialogClose>
                    <Button type="submit" @click="submit" class="w-full sm:w-auto">
                        Simpan
                    </Button>
                </DialogFooter>
            </ScrollArea>
        </DialogContent>
    </Dialog>
</template>
