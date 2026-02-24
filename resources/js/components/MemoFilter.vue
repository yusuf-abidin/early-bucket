<script setup lang="ts">
import {
    CalendarDate,
    getLocalTimeZone,
    parseDate,
} from '@internationalized/date';
import { inject, Ref, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowUpDown, Plus, Search, Settings2 } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { cn, df } from '@/lib/utils';
import { CalendarIcon } from 'lucide-vue-next';
import { RangeCalendar } from '@/components/ui/range-calendar';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';

const props = withDefaults(
    defineProps<{
        users: { id: number; name: string }[];
        initialFilters?: {
            search?: string;
            receivedFrom?: string;
            receivedTo?: string;
            userIds?: number[];
            sortBy?: string;
            sortDir?: 'asc' | 'desc';
            dateBy?: string;
        };
        mode?: 'index' | 'history';
        defaultColumns: Record<string, boolean>;
    }>(),
    {
        mode: 'index',
    },
);

const isOpen = defineModel<boolean>('isOpen', { default: false });

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
const createFormMemo = () => {
    isOpen.value = true;
};

const searchQuery = ref(props.initialFilters?.search || '');
const dateRange = ref<{
    start: CalendarDate | undefined;
    end: CalendarDate | undefined;
}>({
    start: parseSafeDate(props.initialFilters?.receivedFrom),
    end: parseSafeDate(props.initialFilters?.receivedTo),
});

const selectedUsers = ref<number[]>(props.initialFilters?.userIds || []);
const dateBy = ref<string>(props.initialFilters?.dateBy || 'received_at');
const sortBy = ref(props.initialFilters?.sortBy || 'received_at');
const sortDir = ref<'asc' | 'desc'>(props.initialFilters?.sortDir || 'desc');

const visibleColumns = inject<Ref<Record<string, boolean>>>('visibleColumns');

const sortableColumns = [
    { value: 'received_at', label: 'Tanggal Masuk' },
    { value: 'category', label: 'Kategori' },
    { value: 'due_date', label: 'Deadline' },
];

const resetColumns = () => {
    visibleColumns!.value = { ...props.defaultColumns };
};

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
        date_by: dateBy.value,
    };

    router.get(window.location.pathname, query, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
};

watch(
    [dateRange, selectedUsers, sortBy, sortDir, dateBy],
    () => {
        applyFilters();
    },
    { deep: true },
);

const toggleSortDir = () => {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
};
</script>

<template>
    <div
        class="flex w-full flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-center lg:justify-between"
    >
        <!-- Search Input -->
        <div class="relative w-full lg:max-w-sm">
            <div class="flex items-center gap-2">
                <!-- Search input group -->
                <div class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Cari memo"
                        class="min-w-[150px] pl-9"
                        @keyup.enter="applyFilters"
                    />
                </div>

                <!-- Tombol -->
                <Button variant="secondary" size="sm" @click="applyFilters">
                    Cari
                </Button>
            </div>
        </div>

        <!-- Filter Buttons Container -->
        <div class="flex min-w-0 flex-wrap items-center gap-2">
            <!-- Date Range Filter -->
            <Popover>
                <PopoverTrigger as-child>
                    <Button
                        variant="outline"
                        size="sm"
                        :class="
                            cn(
                                'w-full justify-start text-left font-normal sm:w-[220px]',
                                !dateRange && 'text-muted-foreground',
                            )
                        "
                    >
                        <CalendarIcon class="mr-2 h-4 w-4 shrink-0" />
                        <span class="truncate">
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
                        </span>
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-auto p-3" align="start">
                    <div class="mb-3 border-b border-border pb-3">
                        <label
                            class="mb-1.5 block text-xs font-medium text-muted-foreground"
                        >
                            Berdasarkan
                        </label>
                        <Select v-model="dateBy">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Filter Tanggal" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="received_at">
                                    Tanggal Masuk
                                </SelectItem>
                                <SelectItem value="due_date">
                                    Deadline
                                </SelectItem>
                                <SelectItem
                                    value="completed_at"
                                    v-if="mode === 'history'"
                                >
                                    Tanggal Selesai
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
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

            <!-- Sort Filter -->
            <Popover>
                <PopoverTrigger as-child>
                    <Button
                        variant="outline"
                        size="sm"
                        class="w-full sm:w-auto"
                    >
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
                    <Button
                        variant="outline"
                        size="sm"
                        class="w-full sm:w-auto"
                    >
                        <Settings2 class="mr-2 h-4 w-4" />
                        Kolom
                    </Button>
                </PopoverTrigger>
                <PopoverContent class="w-56" align="end">
                    <div class="space-y-4">
                        <div>
                            <h4 class="mb-3 text-sm font-medium">
                                Tampilkan kolom
                            </h4>
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-received-at"
                                        v-model="visibleColumns!.received_at"
                                    />
                                    <label
                                        for="col-received-at"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Tanggal Masuk
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-origin"
                                        v-model="visibleColumns!.origin"
                                    />
                                    <label
                                        for="col-origin"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Asal Memo/Surat
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-reference-number"
                                        v-model="
                                            visibleColumns!.reference_number
                                        "
                                    />
                                    <label
                                        for="col-reference-number"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Nomor Memo/Surat
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-subject"
                                        v-model="visibleColumns!.subject"
                                    />
                                    <label
                                        for="col-subject"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Perihal
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-due_date"
                                        v-model="visibleColumns!.due_date"
                                    />
                                    <label
                                        for="col-due_date"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Deadline
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
                                        Sifat
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-document-link"
                                        v-model="visibleColumns!.document_link"
                                    />
                                    <label
                                        for="col-document-link"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Link
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-pic"
                                        v-model="visibleColumns!.assignedUser"
                                    />
                                    <label
                                        for="col-pic"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        PIC
                                    </label>
                                </div>

                                <div
                                    class="flex items-center space-x-2"
                                    v-if="mode === 'index'"
                                >
                                    <Checkbox
                                        id="col-check"
                                        v-model="visibleColumns!.check"
                                    />
                                    <label
                                        for="col-check"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Done
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="col-follow-up-note"
                                        v-model="visibleColumns!.follow_up_note"
                                    />
                                    <label
                                        for="col-follow-up-note"
                                        class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        Tindak Lanjut
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
                                Atur ulang
                            </Button>
                        </div>
                    </div>
                </PopoverContent>
            </Popover>

            <!-- Register Memo Button -->
            <template v-if="mode === 'index'">
                <Button
                    size="sm"
                    @click="createFormMemo"
                    class="w-full sm:w-auto"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    Register Memo
                </Button>
            </template>
        </div>
    </div>
</template>

<style scoped></style>
