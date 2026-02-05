<script setup lang="ts">
import { inject, Ref, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Search,
    Settings2,
    Calendar as CalendarIcon,
    ArrowUpDown,
} from 'lucide-vue-next';
import { cn, df } from '@/lib/utils';
import { RangeCalendar } from '@/components/ui/range-calendar';
import {
    CalendarDate,
    getLocalTimeZone,
    parseDate,
} from '@internationalized/date'; // Asumsi utility untuk classnames

// Props: Daftar users untuk filter (asumsi UserTaskSummary atau similar dari sebelumnya)
const props = defineProps<{
    users: { id: number; name: string }[]; // Daftar users untuk multiple select
    initialFilters?: {
        search?: string;
        createdFrom?: string;
        createdTo?: string;
        userIds?: number[];
        sortBy?: string;
        sortDir?: 'asc' | 'desc';
    };
}>();

// Helper untuk validasi string tanggal sebelum di-parse
const parseSafeDate = (
    dateStr: string | undefined,
): CalendarDate | undefined => {
    if (!dateStr) return undefined;
    try {
        // Asumsi dateStr berformat "YYYY-MM-DD"
        return parseDate(dateStr.split('T')[0]);
    } catch (e) {
        return undefined;
    }
};

// State
const searchQuery = ref(props.initialFilters?.search || '');
const dateRange = ref<{
    start: CalendarDate | undefined;
    end: CalendarDate | undefined;
}>({
    start: parseSafeDate(props.initialFilters?.createdFrom),
    end: parseSafeDate(props.initialFilters?.createdTo),
});
const selectedUsers = ref<number[]>(props.initialFilters?.userIds || []);
const sortBy = ref(props.initialFilters?.sortBy || 'created_at');
const sortDir = ref<'asc' | 'desc'>(props.initialFilters?.sortDir || 'desc');

const defaultColumns = {
    task: true,
    assignedUser: true,
    createdDate: true,
    dueDate: true,
    resolvedDate: true,
    category: true,
    notes: true,
};

const visibleColumns = inject<Ref<Record<string, boolean>>>('visibleColumns');

// Kolom yang bisa di-sort
const sortableColumns = [
    { value: 'task_description', label: 'Agenda' },
    { value: 'created_at', label: 'Tanggal Dibuat' },
    { value: 'due_date', label: 'Deadline' },
    { value: 'completed_at', label: 'Tanggal Diselesaikan' },
    { value: 'category', label: 'Kategori' },
];

// Fungsi reset columns
const resetColumns = () => {
    visibleColumns!.value = { ...defaultColumns };
};

// Fungsi apply filters: Update query params via Inertia router
const applyFilters = () => {
    const query: Record<string, any> = {
        search: searchQuery.value || undefined,
        date_from: dateRange.value.start
            ? dateRange.value.start.toString()
            : undefined,
        date_to: dateRange.value.end
            ? dateRange.value.end.toString()
            : undefined,
        user_ids:
            selectedUsers.value.length > 0 ? selectedUsers.value : undefined,
        sort_by: sortBy.value,
        sort_dir: sortDir.value,
    };

    router.get(window.location.pathname, query, {
        preserveState: true,
        replace: true,
    });
};

// Watch changes dan apply filters
watch(
    [dateRange, selectedUsers, sortBy, sortDir],
    () => {
        applyFilters();
    },
    { deep: true },
);

// Fungsi toggle sort direction
const toggleSortDir = () => {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
};
</script>

<template>
    <div class="flex items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative max-w-sm flex-1 flex items-center gap-2">
            <div class="relative flex-1">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    placeholder="Cari agenda"
                    class="pl-9"
                    @keyup.enter="applyFilters"
                />
            </div>
            <Button
                variant="secondary"
                size="sm"
                @click="applyFilters"
            >
                Search
            </Button>
        </div>

        <div class="flex items-center gap-2">
            <!-- Date Range Filter -->
            <Popover>
                <PopoverTrigger as-child>
                    <Button
                        variant="outline"
                        size="sm"
                        :class="
                            cn(
                                'w-[220px] justify-start text-left font-normal',
                                !dateRange && 'text-muted-foreground',
                            )
                        "
                    >
                        <CalendarIcon class="mr-2 h-4 w-4" />
                        <template v-if="dateRange.start">
                            <template v-if="dateRange.end">
                                {{
                                    df.format(
                                        dateRange.start.toDate(
                                            getLocalTimeZone(),
                                        ),
                                    )
                                }}
                                -
                                {{
                                    df.format(
                                        dateRange.end.toDate(
                                            getLocalTimeZone(),
                                        ),
                                    )
                                }}
                            </template>
                            <template v-else>
                                {{
                                    df.format(
                                        dateRange.start.toDate(
                                            getLocalTimeZone(),
                                        ),
                                    )
                                }}
                            </template>
                        </template>
                        <span v-else>Filter tanggal</span>
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-0" align="start">
                    <RangeCalendar
                        v-model="dateRange"
                        class="rounded-md border shadow-sm"
                        :number-of-months="1"
                        disable-days-outside-current-view
                    />
                </PopoverContent>
            </Popover>

            <!-- User Filter (Multiple Select) -->
            <Select v-model="selectedUsers" multiple>
                <SelectTrigger class="w-[180px]">
                    <SelectValue placeholder="Filter user" />
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

            <!-- Sort Filter -->
            <Popover>
                <PopoverTrigger as-child>
                    <Button variant="outline" size="sm">
                        <ArrowUpDown class="mr-2 h-4 w-4" />
                        Sort
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-56" align="end">
                    <div class="space-y-4">
                        <h4 class="mb-3 text-sm font-medium">Sort By</h4>
                        <Select v-model="sortBy">
                            <SelectTrigger>
                                <SelectValue
                                    :placeholder="
                                        sortableColumns.find(
                                            (c) => c.value === sortBy,
                                        )?.label
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="col in sortableColumns"
                                    :key="col.value"
                                    :value="col.value"
                                >
                                    {{ col.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Button
                            variant="outline"
                            size="sm"
                            class="w-full"
                            @click="toggleSortDir"
                        >
                            Direction: {{ sortDir.toUpperCase() }}
                        </Button>
                    </div>
                </PopoverContent>
            </Popover>

            <!-- Column Filter -->
            <Popover>
                <PopoverTrigger as-child>
                    <Button variant="outline" size="sm">
                        <Settings2 class="mr-2 h-4 w-4" />
                        Columns
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-56" align="end">
                    <div class="space-y-4">
                        <div>
                            <h4 class="mb-3 text-sm font-medium">
                                Toggle Columns
                            </h4>
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-task"
                                        v-model="visibleColumns!.task"
                                    />
                                    <label
                                        for="col-task"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Agenda
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-assigned-user"
                                        v-model="visibleColumns!.assignedUser"
                                    />
                                    <label
                                        for="col-assigned-user"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        PIC
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-created-date"
                                        v-model="visibleColumns!.createdDate"
                                    />
                                    <label
                                        for="col-created-date"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Tanggal Dibuat
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-due-date"
                                        v-model="visibleColumns!.dueDate"
                                    />
                                    <label
                                        for="col-due-date"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Deadline
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-is-resolved"
                                        v-model="visibleColumns!.resolvedDate"
                                    />
                                    <label
                                        for="col-is-resolved"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Tanggal Selesai
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-category"
                                        v-model="visibleColumns!.category"
                                    />
                                    <label
                                        for="col-category"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Kategori
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-notes"
                                        v-model="visibleColumns!.notes"
                                    />
                                    <label
                                        for="col-notes"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Notes
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="border-t pt-2">
                            <Button
                                variant="ghost"
                                size="sm"
                                class="w-full"
                                @click="resetColumns"
                            >
                                Reset to Default
                            </Button>
                        </div>
                    </div>
                </PopoverContent>
            </Popover>
        </div>
    </div>
</template>
