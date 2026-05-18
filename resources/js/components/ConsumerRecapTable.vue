<script setup lang="ts">
import { ref, reactive, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { monthGradients, monthList } from '@/lib/utils';
import consumerRecap from '@/routes/consumer-recap';

const props = defineProps<{
    consumer_recaps: any;
    selected_year: number;
}>();

const activeInput = ref<HTMLInputElement | null>(null);

const activeCell = ref<string | null>(null);

const localData = reactive<
    Record<string, { consumer: string; percent: string }>
>({});

// Track saving/error state per cell
const savingCells = reactive<Record<string, boolean>>({});
const errorCells = reactive<Record<string, boolean>>({});

function cellKey(monthIndex: number, date: number) {
    return `${monthIndex}-${date}`;
}

function editKey(monthIndex: number, date: number, field: string) {
    return `${monthIndex}-${date}-${field}`;
}

function getDayData(monthIndex: number, date: number) {
    const month = props.consumer_recaps[monthIndex];
    return month?.days?.find((d: any) => d.date === date) ?? null;
}

function getValue(
    monthIndex: number,
    date: number,
    field: 'consumer' | 'percent',
) {
    const key = cellKey(monthIndex, date);
    if (localData[key] !== undefined) {
        return localData[key][field];
    }
    const day = getDayData(monthIndex, date);
    return day?.[field] ?? '';
}

async function startEdit(monthIndex: number, date: number, field: string) {
    const key = cellKey(monthIndex, date);
    activeCell.value = editKey(monthIndex, date, field);

    if (!localData[key]) {
        const day = getDayData(monthIndex, date);
        localData[key] = {
            consumer: day?.consumer ?? '',
            percent: day?.percent ?? '',
        };
    }
    await nextTick();
    activeInput.value?.focus();
    activeInput.value?.select();
}

function onInput(
    monthIndex: number,
    date: number,
    field: 'consumer' | 'percent',
    value: string,
) {
    const key = cellKey(monthIndex, date);
    if (!localData[key]) {
        localData[key] = { consumer: '', percent: '' };
    }
    localData[key][field] = value;
    errorCells[editKey(monthIndex, date, field)] = false;
}

function onBlur(
    monthIndex: number,
    date: number,
    field: 'consumer' | 'percent',
) {
    activeCell.value = null;
    saveCell(monthIndex, date, field);
}

function onKeydown(
    e: KeyboardEvent,
    monthIndex: number,
    date: number,
    field: 'consumer' | 'percent',
) {
    if (e.key === 'Enter') {
        (e.target as HTMLElement).blur();
    }
    if (e.key === 'Escape') {
        // Rollback local state
        const key = cellKey(monthIndex, date);
        const day = getDayData(monthIndex, date);
        if (localData[key]) {
            localData[key][field] = day?.[field] ?? '';
        }
        activeCell.value = null;
    }
    // Tab to next field
    if (e.key === 'Tab') {
        e.preventDefault();
        (e.target as HTMLElement).blur();
        const nextField = field === 'consumer' ? 'percent' : 'consumer';
        const nextDate = field === 'percent' ? date + 1 : date;
        // Small delay to allow blur to process
        setTimeout(() => startEdit(monthIndex, nextDate, nextField), 50);
    }
}

function saveCell(
    monthIndex: number,
    date: number,
    field: 'consumer' | 'percent',
) {
    const key = cellKey(monthIndex, date);
    const eKey = editKey(monthIndex, date, field);
    const day = getDayData(monthIndex, date);

    const currentValue = localData[key]?.[field] ?? '';
    const originalValue = day?.[field] ?? '';

    // Skip if value unchanged
    if (String(currentValue) === String(originalValue)) return;

    savingCells[eKey] = true;

    // Adjust route/params sesuai kebutuhan Anda
    router.patch(
        consumerRecap.upsert().url,
        {
            year: props.selected_year,
            month: monthIndex + 1,
            date: date,
            field: field,
            value: currentValue,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                savingCells[eKey] = false;
                errorCells[eKey] = false;
            },
            onError: () => {
                // Rollback on error
                savingCells[eKey] = false;
                errorCells[eKey] = true;
                if (localData[key]) {
                    localData[key][field] = String(originalValue);
                }
            },
        },
    );
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
</script>

<template>
    <div class="flex gap-6 overflow-x-auto rounded-md pb-4">
        <div
            v-for="(month, monthIndex) in consumer_recaps"
            :key="monthIndex"
            class="h-fit w-80 shrink-0 overflow-x-auto rounded-xl border border-gray-200 shadow-sm"
        >
            <div class="grid grid-cols-3 text-center">
                <!-- Header bulan -->
                <div
                    :class="getMonthBackground(monthIndex + 1)"
                    class="col-span-3 py-2 text-lg font-bold text-white"
                >
                    {{
                        monthList.find((m) => m.value === monthIndex + 1)?.label
                    }}
                    {{ selected_year }}
                </div>

                <!-- Column headers -->
                <div
                    class="border-r border-b border-gray-200 py-1.5 text-sm font-bold"
                >
                    Tanggal
                </div>
                <div
                    class="border-r border-b border-gray-200 py-1.5 text-sm font-bold"
                >
                    Kons
                </div>
                <div class="border-b border-gray-200 py-1.5 text-sm font-bold">
                    %
                </div>

                <!-- Rows: tanggal 1–31 -->
                <template v-for="date in 31" :key="date">
                    <!-- Kolom tanggal (tidak editable) -->
                    <div
                        class="border-r border-b border-gray-100 p-1.5 text-sm"
                    >
                        {{ date }}
                    </div>

                    <!-- Kolom consumer (editable) -->
                    <div
                        class="relative border-r border-b border-gray-100 text-sm"
                        :class="{
                            'bg-blue-50 ring-1 ring-blue-400 ring-inset':
                                activeCell ===
                                editKey(monthIndex, date, 'consumer'),
                            'bg-red-50':
                                errorCells[
                                    editKey(monthIndex, date, 'consumer')
                                ],
                        }"
                        @click="startEdit(monthIndex, date, 'consumer')"
                    >
                        <!-- Saving indicator -->
                        <span
                            v-if="
                                savingCells[
                                    editKey(monthIndex, date, 'consumer')
                                ]
                            "
                            class="absolute top-1 right-1 h-1.5 w-1.5 animate-pulse rounded-full bg-blue-400"
                        />

                        <input
                            v-if="
                                activeCell ===
                                editKey(monthIndex, date, 'consumer')
                            "
                            :ref="
                                (el) => {
                                    activeInput = el as HTMLInputElement | null;
                                }
                            "
                            type="number"
                            class="w-full bg-transparent p-1.5 text-center text-sm text-gray-800 outline-none"
                            :value="getValue(monthIndex, date, 'consumer')"
                            @input="
                                onInput(
                                    monthIndex,
                                    date,
                                    'consumer',
                                    ($event.target as HTMLInputElement).value,
                                )
                            "
                            @blur="onBlur(monthIndex, date, 'consumer')"
                            @keydown="
                                onKeydown($event, monthIndex, date, 'consumer')
                            "
                            autofocus
                        />
                        <span v-else class="block cursor-pointer p-1.5">
                            {{
                                formatRibuan(
                                    getValue(monthIndex, date, 'consumer'),
                                )
                            }}
                        </span>
                    </div>

                    <!-- Kolom percent (editable) -->
                    <div
                        class="relative border-b border-gray-100 text-sm"
                        :class="{
                            'bg-blue-50 ring-1 ring-blue-400 ring-inset':
                                activeCell ===
                                editKey(monthIndex, date, 'percent'),
                            'bg-red-50':
                                errorCells[
                                    editKey(monthIndex, date, 'percent')
                                ],
                        }"
                        @click="startEdit(monthIndex, date, 'percent')"
                    >
                        <!-- Saving indicator -->
                        <span
                            v-if="
                                savingCells[
                                    editKey(monthIndex, date, 'percent')
                                ]
                            "
                            class="absolute top-1 right-1 h-1.5 w-1.5 animate-pulse rounded-full bg-blue-400"
                        />

                        <input
                            v-if="
                                activeCell ===
                                editKey(monthIndex, date, 'percent')
                            "
                            :ref="
                                (el) => {
                                    activeInput = el as HTMLInputElement | null;
                                }
                            "
                            type="number"
                            class="w-full bg-transparent p-1.5 text-center text-sm text-gray-800 outline-none"
                            :value="getValue(monthIndex, date, 'percent')"
                            @input="
                                onInput(
                                    monthIndex,
                                    date,
                                    'percent',
                                    ($event.target as HTMLInputElement).value,
                                )
                            "
                            @blur="onBlur(monthIndex, date, 'percent')"
                            @keydown="
                                onKeydown($event, monthIndex, date, 'percent')
                            "
                            autofocus
                        />
                        <span v-else class="block cursor-pointer p-1.5">
                            {{ getValue(monthIndex, date, 'percent') }}
                        </span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Hilangkan spinner number input */
input[type='number']::-webkit-inner-spin-button,
input[type='number']::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type='number'] {
    -moz-appearance: textfield;
}
</style>
