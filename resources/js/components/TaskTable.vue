<script setup lang="ts">
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
    DropdownMenuItem,
} from '@/components/ui/dropdown-menu';
import {
    EllipsisVertical,
    Settings2,
    Pencil,
    Trash2,
    Search,
    Plus,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    CalendarIcon,
} from 'lucide-vue-next';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { computed, ref, watch } from 'vue';
import { Category, Task, User } from '@/types';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Popover,
    PopoverTrigger,
    PopoverContent,
} from '@/components/ui/popover';
import { Badge } from '@/components/ui/badge';
import { useTableResize } from '@/composables/useTableResize';
import '@/assets/styles/table-resize.css';
import { cn, df, getBadgeColor } from '@/lib/utils';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { router } from '@inertiajs/vue3';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { CalendarDate, getLocalTimeZone } from '@internationalized/date';
import { RangeCalendar } from '@/components/ui/range-calendar';

const dialogDeleteTask = defineModel<boolean>('dialogDeleteTask', {
    default: false,
});
const dialogResolveTask = defineModel<boolean>('dialogResolveTask', {
    default: false,
});
const selectedData = defineModel<Task | null>('selectedData', {
    default: null,
});
const formIsOpen = defineModel<boolean>('formIsOpen', {
    default: false,
});

const props = defineProps<{
    tasksData: Task[];
    usersData: User[];
    categories: Category[];
    initialFilters?: {
        userIds?: number[];
        date_from: string;
        date_to: string;
    };
}>();
const searchQuery = ref('');

const defaultColumns = {
    task: true,
    assignedUser: true,
    dueDate: true,
    isResolved: true,
    category: true,
    notes: true,
};

const parseSafeDate = (
    dateStr: string | undefined,
): CalendarDate | undefined => {
    if (!dateStr) return undefined;
    try {
        return parseSafeDate(dateStr.split('T')[0]);
    } catch (e) {
        return undefined;
    }
};

const dateRange = ref<{
    start: CalendarDate | undefined;
    end: CalendarDate | undefined;
}>({
    start: parseSafeDate(props.initialFilters?.date_from),
    end: parseSafeDate(props.initialFilters?.date_to),
});

const getStoredColumns = () => {
    const stored = localStorage.getItem('tasksTableColumns');
    return stored ? JSON.parse(stored) : defaultColumns;
};
const visibleColumns = ref(getStoredColumns());

watch(
    visibleColumns,
    (newValue) => {
        localStorage.setItem('tasksTableColumns', JSON.stringify(newValue));
    },
    { deep: true },
);

const sortDirection = ref<'asc' | 'desc' | null>(null);

const toggleSort = () => {
    if (sortDirection.value === null) {
        sortDirection.value = 'asc';
    } else if (sortDirection.value === 'asc') {
        sortDirection.value = 'desc';
    } else {
        sortDirection.value = null;
    }
};

const sortedTasks = computed(() => {
    let tasks = props.tasksData;

    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        tasks = tasks.filter((task) => {
            return (
                task.task_description.toLowerCase().includes(query) ||
                (task.category?.name?.toLowerCase().includes(query) ?? false) ||
                (task.notes?.toLowerCase().includes(query) ?? false) ||
                task.users.some(
                    (user) =>
                        user.name.toLowerCase().includes(query) ||
                        user.email.toLowerCase().includes(query),
                )
            );
        });
    }

    if (sortDirection.value) {
        tasks = [...tasks].sort((a, b) => {
            const dateA = new Date(a.due_date);
            const dateB = new Date(b.due_date);
            if (sortDirection.value === 'asc') {
                return dateA.getTime() - dateB.getTime();
            } else {
                return dateB.getTime() - dateA.getTime();
            }
        });
    }

    return tasks;
});

const openCreateModal = () => {
    selectedData.value = null;
    formIsOpen.value = true;
};

const openEditModal = (task: Task) => {
    selectedData.value = task;
    formIsOpen.value = true;
};

const handleDelete = (task: Task) => {
    dialogDeleteTask.value = true;
    selectedData.value = task;
};

const handleResolveTask = (task: Task) => {
    dialogResolveTask.value = true;
    selectedData.value = task;
};

const resetColumns = () => {
    visibleColumns.value = { ...defaultColumns };
};

const { columnWidths, startResize } = useTableResize({
    storageKey: 'task-table-column-widths',
    defaultWidths: {
        no: 20,
        task: 200,
        assignedUser: 150,
        dueDate: 120,
        isResolved: 80,
        category: 120,
        notes: 250,
    },
    minWidth: 20,
});

const selectedUsers = ref<number[]>(props.initialFilters?.userIds || []);
const applyFilters = () => {
    const query: Record<string, any> = {
        user_ids:
            selectedUsers.value.length > 0 ? selectedUsers.value : undefined,
        date_from: dateRange.value.start
            ? dateRange.value.start.toString()
            : undefined,
        date_to: dateRange.value.end
            ? dateRange.value.end.toString()
            : undefined,
    };

    router.get(window.location.pathname, query, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
};

watch(
    [dateRange, selectedUsers],
    () => {
        applyFilters();
    },
    { deep: true },
);
</script>

<template>
    <div class="space-y-4">
        <!-- Header dengan Search dan Filter -->
        <div
            class="flex w-full flex-col gap-4 lg:flex-row lg:flex-wrap lg:items-center lg:justify-between"
        >
            <div class="relative w-full lg:max-w-sm">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    placeholder="Cari agenda"
                    class="min-w-[150px] pl-9"
                />
            </div>

            <div class="flex flex-wrap items-center gap-2">
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
                                <span v-else>Filter deadline</span>
                            </span>
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
                    <SelectTrigger class="w-full sm:w-[180px]">
                        <SelectValue placeholder="Filter pengguna" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="user in props.usersData"
                            :key="user.id"
                            :value="user.id"
                        >
                            {{ user.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

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
                                            id="col-task"
                                            v-model="visibleColumns.task"
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
                                            v-model="
                                                visibleColumns.assignedUser
                                            "
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
                                            id="col-due-date"
                                            v-model="visibleColumns.dueDate"
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
                                            v-model="visibleColumns.isResolved"
                                        />
                                        <label
                                            for="col-is-resolved"
                                            class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        >
                                            Check
                                        </label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Checkbox
                                            id="col-category"
                                            v-model="visibleColumns.category"
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
                                            v-model="visibleColumns.notes"
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
                                    Atur ulang
                                </Button>
                            </div>
                        </div>
                    </PopoverContent>
                </Popover>
                <Button
                    size="sm"
                    @click="openCreateModal"
                    class="w-full sm:w-auto"
                >
                    <Plus class="mr-2 h-4 w-4" />
                    Buat Agenda
                </Button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-lg border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead
                            class="relative font-bold"
                            :style="{
                                width: columnWidths.no + 'px',
                                minWidth: columnWidths.no + 'px',
                            }"
                        >
                            No
                            <div
                                class="resize-handle"
                                @mousedown="(e) => startResize(e, 'no')"
                            ></div>
                        </TableHead>
                        <TableHead
                            class="relative font-bold"
                            :style="{
                                width: columnWidths.task + 'px',
                                minWidth: columnWidths.task + 'px',
                            }"
                            v-if="visibleColumns.task"
                        >
                            Agenda
                            <div
                                class="resize-handle"
                                @mousedown="(e) => startResize(e, 'task')"
                            ></div
                        ></TableHead>
                        <TableHead
                            class="relative font-bold"
                            :style="{
                                width: columnWidths.assignedUser + 'px',
                                minWidth: columnWidths.assignedUser + 'px',
                            }"
                            v-if="visibleColumns.assignedUser"
                        >
                            PIC
                            <div
                                class="resize-handle"
                                @mousedown="
                                    (e) => startResize(e, 'assignedUser')
                                "
                            ></div>
                        </TableHead>
                        <TableHead
                            v-if="visibleColumns.dueDate"
                            class="relative cursor-pointer font-bold"
                            @click="toggleSort"
                            :style="{
                                width: columnWidths.dueDate + 'px',
                                minWidth: columnWidths.dueDate + 'px',
                            }"
                        >
                            <div class="flex items-center gap-1">
                                Deadline
                                <component
                                    :is="
                                        sortDirection === null
                                            ? ArrowUpDown
                                            : sortDirection === 'asc'
                                              ? ArrowUp
                                              : ArrowDown
                                    "
                                    class="h-4 w-4"
                                />
                            </div>
                            <div
                                class="resize-handle"
                                @mousedown="(e) => startResize(e, 'dueDate')"
                            ></div>
                        </TableHead>
                        <TableHead
                            :style="{
                                width: columnWidths.isResolved + 'px',
                                minWidth: columnWidths.isResolved + 'px',
                            }"
                            class="relative text-center font-bold"
                            v-if="visibleColumns.isResolved"
                        >
                            Check
                            <div
                                class="resize-handle"
                                @mousedown="(e) => startResize(e, 'isResolved')"
                            ></div>
                        </TableHead>
                        <TableHead
                            class="relative font-bold"
                            :style="{
                                width: columnWidths.category + 'px',
                                minWidth: columnWidths.category + 'px',
                            }"
                            v-if="visibleColumns.category"
                        >
                            Kategori
                            <div
                                class="resize-handle"
                                @mousedown="(e) => startResize(e, 'category')"
                            ></div>
                        </TableHead>
                        <TableHead
                            class="relative font-bold"
                            :style="{
                                width: columnWidths.notes + 'px',
                                minWidth: columnWidths.notes + 'px',
                            }"
                            v-if="visibleColumns.notes"
                        >
                            Notes
                            <div
                                class="resize-handle"
                                @mousedown="(e) => startResize(e, 'notes')"
                            ></div>
                        </TableHead>
                        <TableHead class="w-[50px]"></TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="sortedTasks.length === 0">
                        <TableCell
                            :colspan="
                                Object.values(visibleColumns).filter(Boolean)
                                    .length + 1
                            "
                            class="text-center text-muted-foreground"
                        >
                            Tidak ada data
                        </TableCell>
                    </TableRow>
                    <TableRow
                        v-for="(task, index) in sortedTasks"
                        :key="task.id"
                    >
                        <!-- No -->
                        <TableCell class="text-center font-medium">
                            {{ index + 1 }}
                        </TableCell>

                        <!-- AGENDA -->
                        <TableCell
                            v-if="visibleColumns.task"
                            class="max-w-[400px] font-medium wrap-break-word whitespace-pre-line"
                        >
                            {{ task.task_description }}
                        </TableCell>
                        <TableCell
                            class="resizable-cell wrap"
                            v-if="visibleColumns.assignedUser"
                        >
                            <div class="flex flex-row flex-wrap gap-1">
                                <Badge
                                    v-for="user in task.users"
                                    :key="user.id"
                                    :class="
                                        getBadgeColor(
                                            user.color?.name ?? 'Abu-Abu',
                                        )
                                    "
                                >
                                    {{ user.name }}
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell v-if="visibleColumns.dueDate">
                            <TooltipProvider v-if="task.due_date_updated_at">
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Badge
                                            variant="outline"
                                            class="border-yellow-400 bg-yellow-200 text-yellow-800"
                                        >
                                            {{ task.due_date }}
                                        </Badge>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>
                                            Deadline telah diubah pada
                                            {{ task.due_date_updated_at }}
                                        </p>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>

                            <Badge variant="outline" v-else>
                                {{ task.due_date }}
                            </Badge>
                        </TableCell>
                        <TableCell
                            class="text-center"
                            v-if="visibleColumns.isResolved"
                        >
                            <Checkbox
                                class="h-5 w-5 border-blue-400"
                                :model-value="!!task.completed_at"
                                @click="handleResolveTask(task)"
                            />
                        </TableCell>
                        <TableCell v-if="visibleColumns.category">
                            <Badge
                                :class="
                                    getBadgeColor(
                                        task.category?.color?.name ?? 'Abu-Abu',
                                    )
                                "
                            >
                                {{ task.category?.name }}
                            </Badge>
                        </TableCell>
                        <TableCell v-if="visibleColumns.notes">
                            <div
                                class="max-w-[400px] font-medium wrap-break-word whitespace-normal"
                            >
                                {{ task.notes }}
                            </div>
                        </TableCell>
                        <TableCell>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-8 w-8 p-0"
                                    >
                                        <span class="sr-only">Open menu</span>
                                        <EllipsisVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuLabel>Aksi</DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        @click="openEditModal(task)"
                                    >
                                        <Pencil class="mr-2 h-4 w-4" />
                                        Update
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        @click="handleDelete(task)"
                                        class="text-destructive focus:text-destructive"
                                    >
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Results info -->
        <div class="text-sm text-muted-foreground">
            Menampilkan {{ sortedTasks.length }} dari
            {{ props.tasksData.length }} data
        </div>
    </div>
</template>

<style scoped></style>
