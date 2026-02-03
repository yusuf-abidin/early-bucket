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
</script>

<template>
    <div class="space-y-4">
        <!-- Header dengan Search dan Filter -->
        <div class="flex items-center justify-between gap-4">
            <div class="relative max-w-sm flex-1">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="searchQuery"
                    placeholder="Search agenda"
                    class="pl-9"
                />
            </div>

            <div class="flex items-center gap-2">
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
                                            Category
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
                                    Reset to Default
                                </Button>
                            </div>
                        </div>
                    </PopoverContent>
                </Popover>
                <Button size="sm" @click="openCreateModal">
                    <Plus class="mr-2 h-4 w-4" />
                    Buat Agenda
                </Button>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-lg border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[50px]">No</TableHead>
                        <TableHead v-if="visibleColumns.task">Agenda</TableHead>
                        <TableHead v-if="visibleColumns.assignedUser"
                            >PIC</TableHead
                        >
                        <TableHead
                            v-if="visibleColumns.dueDate"
                            class="cursor-pointer"
                            @click="toggleSort"
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
                        </TableHead>
                        <TableHead
                            class="text-center"
                            v-if="visibleColumns.isResolved"
                            >Check</TableHead
                        >
                        <TableHead v-if="visibleColumns.category"
                            >Kategori</TableHead
                        >
                        <TableHead v-if="visibleColumns.notes">Notes</TableHead>
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
                            No tasks found
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
                        <TableCell v-if="visibleColumns.assignedUser">
                            <div class="flex flex-col flex-wrap gap-1">
                                <Badge
                                    v-for="user in task.users"
                                    :key="user.id"
                                    :class="
                                        user.color?.class ??
                                        'bg-gray-50 text-gray-600 inset-ring inset-ring-gray-500/10'
                                    "
                                >
                                    {{ user.name }}
                                </Badge>
                            </div>
                        </TableCell>
                        <TableCell v-if="visibleColumns.dueDate">
                            <Badge variant="outline">
                                {{ task.due_date }}
                            </Badge>
                        </TableCell>
                        <TableCell
                            class="text-center"
                            v-if="visibleColumns.isResolved"
                        >
                            <Checkbox
                                class="h-5 w-5"
                                :model-value="!!task.completed_at"
                                @click="handleResolveTask(task)"
                            />
                        </TableCell>
                        <TableCell v-if="visibleColumns.category">
                            <Badge
                                :class="
                                    task.category?.color?.class ??
                                    'bg-gray-50 text-gray-600 inset-ring inset-ring-gray-500/10'
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
                                    <DropdownMenuLabel
                                        >Actions</DropdownMenuLabel
                                    >
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
                                        Delete
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
