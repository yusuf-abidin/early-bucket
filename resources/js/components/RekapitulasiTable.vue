<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Building2,
    MapPin,
    Landmark,
    CalendarIcon,
    Check,
    X,
    Trash2,
    Pencil,
} from 'lucide-vue-next';
import { PerformancePeriod, Regional } from '@/types';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PerformanceCheckbox from '@/components/PerformanceCheckbox.vue';
import performanceLog from '@/routes/performance-log';

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
    months: {
        value: number;
        label: string;
    }[];
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

const editPeriod = defineModel<PerformancePeriod | null>('edit-period', {
    default: null,
});

const handleEditPeriod = (
    id: number | null,
    month: number,
    performance_type: string,
    start_date: number | null,
    end_date: number | null,
) => {
    editPeriod.value = {
        id: id,
        month: month,
        performance_type: performance_type,
        start_date: start_date,
        end_date: end_date,
        year: props.year,
        order: props.periods[month][performance_type].order,
    };
};
const getPeriodTypes = (month: number) => {
    return Object.keys(props.periods[month] || {});
};

const loadingCells = ref<Set<string>>(new Set());

function cellKey(
    month: number,
    type: string,
    entityType: string,
    entityId: number,
): string {
    return `${month}_${type}_${entityType}_${entityId}`;
}

function isLoading(
    month: number,
    type: string,
    entityType: string,
    entityId: number,
): boolean {
    return loadingCells.value.has(cellKey(month, type, entityType, entityId));
}

function getLog(
    month: number,
    type: string,
    entityType: 'regional' | 'area' | 'branch',
    entityId: number,
) {
    const period = props.periods[month]?.[type];
    if (!period?.id) return null;
    return props.log_index[period.id]?.[entityType]?.[entityId] ?? null;
}

const hoveredElement = ref<HTMLElement | null>(null);
const activeCellData = ref<{
    month: number;
    type: string;
    entityType: 'regional' | 'area' | 'branch';
    entityId: number;
} | null>(null);

const isHoveringPopover = ref(false);
const closeTimeout = ref<ReturnType<typeof setTimeout> | null>(null);

const calculatePosition = (el: HTMLElement) => {
    const rect = el.getBoundingClientRect();
    return {
        top: `${rect.top - 48}px`,
        left: `${rect.left + rect.width / 2}px`,
        transform: 'translateX(-50%)',
    };
};

const handleHover = (
    el: HTMLElement,
    month: number,
    type: string,
    entityType: 'regional' | 'area' | 'branch',
    entityId: number,
) => {
    if (closeTimeout.value) clearTimeout(closeTimeout.value);
    hoveredElement.value = el;
    activeCellData.value = { month, type, entityType, entityId };
};

const handleLeave = () => {
    closeTimeout.value = setTimeout(() => {
        if (!isHoveringPopover.value) {
            hoveredElement.value = null;
            activeCellData.value = null;
        }
    }, 300);
};

const keepOpen = () => {
    isHoveringPopover.value = true;
    if (closeTimeout.value) clearTimeout(closeTimeout.value);
};

const closePopover = () => {
    isHoveringPopover.value = false;
    hoveredElement.value = null;
    activeCellData.value = null;
};

function handleCellClick(
    month: number,
    type: string,
    entityType: 'regional' | 'area' | 'branch',
    entityId: number,
    manualSelect: boolean = false,
    manualAchieve?: boolean | null,
) {
    const key = cellKey(month, type, entityType, entityId);

    if (loadingCells.value.has(key)) return;

    const period = props.periods[month][type];
    const log = getLog(month, type, entityType, entityId);

    let newValue: boolean | null;
    if (!manualSelect) {
        const currentStatus = log
            ? log.is_achieved === null
                ? null
                : Boolean(log.is_achieved)
            : null;
        if (currentStatus == true) {
            newValue = false;
        } else if (currentStatus === false) {
            newValue = null;
        } else {
            newValue = true;
        }
    } else if (manualSelect && manualAchieve !== undefined) {
        newValue = manualAchieve;
    } else {
        newValue = null;
    }

    loadingCells.value = new Set(loadingCells.value).add(key);

    router.post(
        performanceLog.upsert().url,
        {
            ...(period.id
                ? { period_id: period.id }
                : {
                      month: period.month,
                      year: props.year,
                      performance_type: period.performance_type,
                  }),
            entity_type: entityType,
            entity_id: entityId,
            is_achieved: newValue,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                const next = new Set(loadingCells.value);
                next.delete(key);
                loadingCells.value = next;
            },
        },
    );
}

const selectedMonth = defineModel<{
    year: number;
    month: number;
    availableType: PerformancePeriod[];
} | null>('selected-month', { default: null });

const handleClickEditMonth = (month: number) => {
    selectedMonth.value = {
        year: props.year,
        month: month,
        availableType: Object.values(props.periods[month] || {}),
    };
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-border shadow-sm">
        <Table>
            <TableHeader class="sticky top-0 z-20 bg-background">
                <TableRow>
                    <TableHead
                        rowspan="2"
                        class="sticky left-0 z-30 border-b bg-background text-center tracking-wider uppercase shadow-[inset_-1px_-1px_0_0_#e2e8f0]"
                    >
                        Wilayah
                    </TableHead>
                    <TableHead
                        class="border-r text-center"
                        v-for="month in months"
                        :key="month.value"
                        :colspan="
                            Math.max(getPeriodTypes(month.value).length, 1)
                        "
                    >
                        <div class="flex items-center justify-center gap-2">
                            {{ month.label }}
                            <button
                                @click="handleClickEditMonth(month.value)"
                                title="Edit Daftar Etape"
                                class="cursor-pointer rounded p-1 hover:bg-blue-100"
                            >
                                <Pencil class="h-3 w-3" />
                            </button>
                        </div>
                    </TableHead>
                    <TableHead class="text-center" colspan="2">
                        Total
                    </TableHead>
                </TableRow>
                <TableRow>
                    <template
                        v-for="month in months"
                        :key="'sub-' + month.value"
                    >
                        <TableHead
                            v-if="getPeriodTypes(month.value).length === 0"
                            class="min-w-[60px] border-r border-b bg-muted/30 shadow-[inset_0_-1px_0_0_#e2e8f0] text-center"
                        >-</TableHead>
                        <TableHead
                            v-for="(type) in getPeriodTypes(
                                month.value,
                            )"
                            :key="type"
                            class="min-w-[60px] border-b bg-muted/30 shadow-[inset_0_-1px_0_0_#e2e8f0] border-r"
                        >
                            <div
                                class="group flex flex-col items-center gap-1.5 py-2"
                            >
                                <span
                                    class="text-[11px] leading-none font-bold tracking-widest text-muted-foreground uppercase"
                                >
                                    {{
                                        type
                                            .replace('_', ' ')
                                            .replace('etape', 'Etape')
                                    }}
                                </span>

                                <div
                                    @click="
                                        handleEditPeriod(
                                            props.periods[month.value][type].id,
                                            props.periods[month.value][type]
                                                .month,
                                            props.periods[month.value][type]
                                                .performance_type,
                                            props.periods[month.value][type]
                                                .start_date,
                                            props.periods[month.value][type]
                                                .end_date,
                                        )
                                    "
                                    class="flex items-center gap-1 rounded-full border border-border bg-background px-2 py-0.5 shadow-sm transition-colors group-hover:border-primary/50 hover:cursor-pointer"
                                >
                                    <CalendarIcon
                                        class="h-3 w-3 text-primary/60"
                                    />
                                    <div
                                        v-if="
                                            props.periods[month.value][type]
                                                .start_date ||
                                            props.periods[month.value][type]
                                                .end_date
                                        "
                                        class="flex items-center text-[10px] font-medium whitespace-nowrap text-foreground/80"
                                    >
                                        <span>{{
                                            props.periods[month.value][type]
                                                .start_date || null
                                        }}</span>
                                        <span
                                            v-if="
                                                props.periods[month.value][type]
                                                    .start_date &&
                                                props.periods[month.value][type]
                                                    .end_date
                                            "
                                            class="mx-1 opacity-50"
                                            >-</span
                                        >
                                        <span>{{
                                            props.periods[month.value][type]
                                                .end_date || null
                                        }}</span>
                                    </div>
                                    <div
                                        v-else
                                        class="flex items-center text-[10px] font-medium whitespace-nowrap text-foreground/80"
                                    >
                                        -
                                    </div>
                                </div>
                            </div>
                        </TableHead>
                    </template>
                    <TableHead class="text-center border-r border-b bg-muted/30 shadow-[inset_0_-1px_0_0_#e2e8f0] text-[11px] leading-none font-bold tracking-widest text-muted-foreground uppercase">ETAPE</TableHead>
                    <TableHead class="text-center border-b bg-muted/30 shadow-[inset_0_-1px_0_0_#e2e8f0] text-[11px] leading-none font-bold tracking-widest text-muted-foreground uppercase">EOM</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template
                    v-for="regional in props.regionals"
                    :key="regional.id"
                >
                    <TableRow>
                        <TableCell
                            class="sticky left-0 bg-background font-bold shadow-[inset_-1px_0_0_0_#e2e8f0]"
                        >
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                                >
                                    <Landmark class="h-3.5 w-3.5" />
                                </div>
                                <div>
                                    <Badge
                                        variant="secondary"
                                        class="mb-0.5 px-1.5 py-0 text-[10px] font-semibold tracking-wider uppercase"
                                    >
                                        Regional
                                    </Badge>
                                    <p
                                        class="text-sm leading-tight font-bold text-foreground"
                                    >
                                        {{ regional.name }}
                                    </p>
                                </div>
                            </div>
                        </TableCell>
                        <template v-for="month in months" :key="month.value">
                            <TableCell
                                v-if="getPeriodTypes(month.value).length === 0"
                                class="border-r"
                            />
                            <TableCell
                                v-for="(type) in getPeriodTypes(
                                    month.value,
                                )"
                                :key="type"
                                class="text-center border-r"
                            >
                                <PerformanceCheckbox
                                    @hover="
                                        (e) =>
                                            handleHover(
                                                e.target as HTMLElement,
                                                month.value,
                                                type,
                                                'regional',
                                                regional.id,
                                            )
                                    "
                                    @leave="handleLeave"
                                    :log="
                                        getLog(
                                            month.value,
                                            type,
                                            'regional',
                                            regional.id,
                                        )
                                    "
                                    :loading="
                                        isLoading(
                                            month.value,
                                            type,
                                            'regional',
                                            regional.id,
                                        )
                                    "
                                    @toggle="
                                        handleCellClick(
                                            month.value,
                                            type,
                                            'regional',
                                            regional.id,
                                        )
                                    "
                                />
                            </TableCell>
                        </template>
                        <TableCell class="text-center border-r text-sm text-foreground/90">
                            {{ props.totals.regional[regional.id]?.etape || 0 }}
                        </TableCell>
                        <TableCell class="text-center text-sm text-foreground/90">
                            {{ props.totals.regional[regional.id]?.eom || 0 }}
                        </TableCell>
                    </TableRow>
                    <template v-for="area in regional.areas" :key="area.id">
                        <TableRow>
                            <TableCell
                                class="sticky left-0 bg-background shadow-[inset_-1px_0_0_0_#e2e8f0]"
                            >
                                <div class="flex items-center gap-2 pl-4">
                                    <div
                                        class="h-full w-0.5 shrink-0 self-stretch rounded-full bg-border"
                                    ></div>
                                    <div
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-sky-50 text-sky-600"
                                    >
                                        <MapPin class="h-3 w-3" />
                                    </div>
                                    <div>
                                        <Badge
                                            variant="outline"
                                            class="mb-0.5 border-sky-200 px-1.5 py-0 text-[10px] font-semibold tracking-wider text-sky-700 uppercase"
                                        >
                                            Area
                                        </Badge>
                                        <p class="text-sm font-semibold">
                                            {{ area.name }}
                                        </p>
                                    </div>
                                </div>
                            </TableCell>
                            <template
                                v-for="month in months"
                                :key="month.value"
                            >
                                <TableCell
                                    v-if="
                                        getPeriodTypes(month.value).length === 0
                                    "
                                    class="border-r"
                                />
                                <TableCell
                                    v-for="(type) in getPeriodTypes(
                                        month.value,
                                    )"
                                    :key="type"
                                    class="text-center border-r"
                                >
                                    <PerformanceCheckbox
                                        @hover="
                                            (e) =>
                                                handleHover(
                                                    e.target as HTMLElement,
                                                    month.value,
                                                    type,
                                                    'area',
                                                    area.id,
                                                )
                                        "
                                        @leave="handleLeave"
                                        :log="
                                            getLog(
                                                month.value,
                                                type,
                                                'area',
                                                area.id,
                                            )
                                        "
                                        :loading="
                                            isLoading(
                                                month.value,
                                                type,
                                                'area',
                                                area.id,
                                            )
                                        "
                                        @toggle="
                                            handleCellClick(
                                                month.value,
                                                type,
                                                'area',
                                                area.id,
                                            )
                                        "
                                    />
                                </TableCell>
                            </template>
                            <TableCell class="text-center border-r text-sm text-foreground/90">
                                {{ props.totals.area[area.id]?.etape || 0 }}
                            </TableCell>
                            <TableCell class="text-center text-sm text-foreground/90">
                                {{ props.totals.area[area.id]?.eom || 0 }}
                            </TableCell>
                        </TableRow>

                        <TableRow
                            v-for="branch in area.branches"
                            :key="branch.id"
                        >
                            <TableCell
                                class="sticky left-0 bg-background shadow-[inset_-1px_0_0_0_#e2e8f0]"
                            >
                                <div class="flex items-center gap-2 pl-10">
                                    <div
                                        class="h-full w-0.5 shrink-0 self-stretch rounded-full bg-border/50"
                                    ></div>
                                    <div
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-muted text-muted-foreground"
                                    >
                                        <Building2 class="h-2.5 w-2.5" />
                                    </div>
                                    <p class="text-sm text-foreground/90">
                                        {{ branch.name }}
                                    </p>
                                </div>
                            </TableCell>
                            <template
                                v-for="month in months"
                                :key="month.value"
                            >
                                <TableCell
                                    v-if="
                                        getPeriodTypes(month.value).length === 0
                                    "
                                    class="border-r"
                                />
                                <TableCell
                                    v-for="(type) in getPeriodTypes(
                                        month.value,
                                    )"
                                    :key="type"
                                    class="text-center border-r"
                                >
                                    <PerformanceCheckbox
                                        @hover="
                                            (e) =>
                                                handleHover(
                                                    e.target as HTMLElement,
                                                    month.value,
                                                    type,
                                                    'branch',
                                                    branch.id,
                                                )
                                        "
                                        @leave="handleLeave"
                                        :log="
                                            getLog(
                                                month.value,
                                                type,
                                                'branch',
                                                branch.id,
                                            )
                                        "
                                        :loading="
                                            isLoading(
                                                month.value,
                                                type,
                                                'branch',
                                                branch.id,
                                            )
                                        "
                                        @toggle="
                                            handleCellClick(
                                                month.value,
                                                type,
                                                'branch',
                                                branch.id,
                                            )
                                        "
                                    />
                                </TableCell>
                            </template>
                            <TableCell class="text-center border-r text-sm text-foreground/90">
                                {{ props.totals.branch[branch.id]?.etape || 0 }}
                            </TableCell>
                            <TableCell class="text-center text-sm text-foreground/90">
                                {{ props.totals.branch[branch.id]?.eom || 0 }}
                            </TableCell>
                        </TableRow>
                    </template>

                    <template
                        v-for="branch in regional.branches"
                        :key="branch.id"
                    >
                        <TableRow>
                            <TableCell
                                class="sticky left-0 bg-background shadow-[inset_-1px_0_0_0_#e2e8f0]"
                            >
                                <div class="flex items-center gap-2 pl-4">
                                    <div
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-muted text-muted-foreground"
                                    >
                                        <Building2 class="h-2.5 w-2.5" />
                                    </div>
                                    <p class="text-sm text-foreground/90">
                                        {{ branch.name }}
                                    </p>
                                </div>
                            </TableCell>
                            <template
                                v-for="month in months"
                                :key="month.value"
                            >
                                <TableCell
                                    v-if="
                                        getPeriodTypes(month.value).length === 0
                                    "
                                    class="border-r"
                                />
                                <TableCell
                                    v-for="(type) in getPeriodTypes(
                                        month.value,
                                    )"
                                    :key="type"
                                    class="text-center border-r"
                                >
                                    <PerformanceCheckbox
                                        @hover="
                                            (e) =>
                                                handleHover(
                                                    e.target as HTMLElement,
                                                    month.value,
                                                    type,
                                                    'branch',
                                                    branch.id,
                                                )
                                        "
                                        @leave="handleLeave"
                                        :log="
                                            getLog(
                                                month.value,
                                                type,
                                                'branch',
                                                branch.id,
                                            )
                                        "
                                        :loading="
                                            isLoading(
                                                month.value,
                                                type,
                                                'branch',
                                                branch.id,
                                            )
                                        "
                                        @toggle="
                                            handleCellClick(
                                                month.value,
                                                type,
                                                'branch',
                                                branch.id,
                                            )
                                        "
                                    />
                                </TableCell>
                            </template>
                            <TableCell class="text-center border-r text-sm text-foreground/90">
                                {{ props.totals.branch[branch.id]?.etape || 0 }}
                            </TableCell>
                            <TableCell class="text-center text-sm text-foreground/90">
                                {{ props.totals.branch[branch.id]?.eom || 0 }}
                            </TableCell>
                        </TableRow>
                    </template>
                </template>
            </TableBody>
        </Table>
        <Teleport to="body">
            <div
                v-if="activeCellData && hoveredElement"
                :style="calculatePosition(hoveredElement)"
                class="fixed z-100 flex gap-2 rounded-lg border bg-white p-2 shadow-xl"
                @mouseenter="keepOpen"
                @mouseleave="closePopover"
            >
                <button
                    @click="
                        handleCellClick(
                            activeCellData.month,
                            activeCellData.type,
                            activeCellData.entityType,
                            activeCellData.entityId,
                            true,
                            true,
                        )
                    "
                    title="Tercapai"
                    class="rounded p-1 hover:bg-green-100"
                >
                    <Check class="h-5 w-5 text-green-600" />
                </button>
                <button
                    @click="
                        handleCellClick(
                            activeCellData.month,
                            activeCellData.type,
                            activeCellData.entityType,
                            activeCellData.entityId,
                            true,
                            false,
                        )
                    "
                    title="Tidak Tercapai"
                    class="rounded p-1 hover:bg-red-100"
                >
                    <X class="h-5 w-5 text-red-600" />
                </button>
                <button
                    @click="
                        handleCellClick(
                            activeCellData.month,
                            activeCellData.type,
                            activeCellData.entityType,
                            activeCellData.entityId,
                            true,
                            null,
                        )
                    "
                    title="Hapus"
                    class="rounded p-1 hover:bg-gray-100"
                >
                    <Trash2 class="h-5 w-5 text-gray-600" />
                </button>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
:deep(table) {
    display: block;
    overflow-y: auto;
    max-height: calc(100vh - 10rem);
    min-height: 570px;
}

:deep(thead) {
    display: table-header-group;
    position: sticky;
    top: 0;
    z-index: 20;
    background: var(--background, #ffffff);
}

:deep(tbody) {
    display: table-row-group;
}

:deep(tr) {
    display: table-row;
    width: 100%;
}

:deep(th),
:deep(td) {
    display: table-cell;
}
</style>
