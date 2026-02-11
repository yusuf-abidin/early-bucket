<script setup lang="ts">
import { Task } from '@/types';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { inject, Ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { EllipsisVertical, Trash2 } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { useTableResize } from '@/composables/useTableResize';
import '@/assets/styles/table-resize.css';
import { getBadgeColor } from '@/lib/utils';

const props = defineProps<{
    tasks: Task[];
}>();

const dialogDeleteTask = defineModel<boolean>('dialogDeleteTaskIsOpen', { default: false });
const selectedTask = defineModel<Task | null>('selectedData', { default: null });

const handleDelete = (task: Task) => {
    dialogDeleteTask.value = true;
    selectedTask.value = task;
};

const { columnWidths, startResize } = useTableResize({
    storageKey: 'task-table-history-column-widths',
    defaultWidths: {
        no: 20,
        task: 200,
        assignedUser: 150,
        createdDate: 120,
        dueDate: 120,
        resolvedDate: 80,
        category: 120,
        notes: 250,
    },
    minWidth: 20,
});

const visibleColumns = inject<Ref<Record<string, boolean>>>('visibleColumns');
</script>

<template>
    <div class="rounded-lg border">
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
                        v-if="visibleColumns!.task"
                    >
                        Agenda
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'task')"
                        ></div>
                    </TableHead>
                    <TableHead
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.assignedUser + 'px',
                            minWidth: columnWidths.assignedUser + 'px',
                        }"
                        v-if="visibleColumns!.assignedUser"
                    >
                        PIC
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'assignedUser')"
                        ></div>
                    </TableHead>
                    <TableHead
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.createdDate + 'px',
                            minWidth: columnWidths.createdDate + 'px',
                        }"
                        v-if="visibleColumns!.createdDate"
                    >
                        Tanggal Dibuat
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'createdDate')"
                        ></div>
                    </TableHead>
                    <TableHead
                        :style="{
                            width: columnWidths.dueDate + 'px',
                            minWidth: columnWidths.dueDate + 'px',
                        }"
                        class="relative font-bold"
                        v-if="visibleColumns!.dueDate"
                    >
                        Deadline
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'dueDate')"
                        ></div>
                    </TableHead>
                    <TableHead
                        :style="{
                            width: columnWidths.resolvedDate + 'px',
                            minWidth: columnWidths.resolvedDate + 'px',
                        }"
                        class="relative font-bold"
                        v-if="visibleColumns!.resolvedDate"
                    >
                        Tanggal Selesai
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'resolvedDate')"
                        ></div>
                    </TableHead>
                    <TableHead
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.category + 'px',
                            minWidth: columnWidths.category + 'px',
                        }"
                        v-if="visibleColumns!.category"
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
                        v-if="visibleColumns!.notes"
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
                <TableRow v-if="props.tasks.length === 0">
                    <TableCell
                        :colspan="
                            Object.values(visibleColumns ?? {}).filter(Boolean)
                                .length + 1
                        "
                        class="text-center text-muted-foreground"
                    >
                        No tasks found
                    </TableCell>
                </TableRow>

                <TableRow v-for="(task, index) in props.tasks" :key="task.id">
                    <!-- No -->

                    <TableCell class="text-center font-medium">{{
                        index + 1
                    }}</TableCell>

                    <!-- AGENDA -->

                    <TableCell
                        v-if="visibleColumns!.task"
                        class="max-w-[350px] font-medium wrap-break-word whitespace-pre-line"
                    >
                        {{ task.task_description }}
                    </TableCell>

                    <!-- PIC -->
                    <TableCell v-if="visibleColumns!.assignedUser">
                        <div class="flex flex-row flex-wrap gap-1">
                            <Badge
                                v-for="user in task.users"
                                :key="user.id"
                                :class="
                                    getBadgeColor(user.color?.name ?? 'Abu-Abu')
                                "
                            >
                                {{ user.name }}
                            </Badge>
                        </div>
                    </TableCell>

                    <!-- TANGGAL DIBUAT -->
                    <TableCell v-if="visibleColumns!.createdDate">
                        <Badge variant="secondary"
                            >{{ task.created_at }}
                        </Badge>
                    </TableCell>

                    <!-- DEADLINE -->
                    <TableCell v-if="visibleColumns!.dueDate">
                        <Badge variant="outline">{{ task.due_date }} </Badge>
                    </TableCell>

                    <!-- TANGGAL SELESAI -->
                    <TableCell v-if="visibleColumns!.resolvedDate">
                        <Badge>{{ task.completed_at }} </Badge>
                    </TableCell>

                    <!-- KATEGORI -->
                    <TableCell v-if="visibleColumns!.category">
                        <Badge
                            :class="
                                getBadgeColor(task.category?.color?.name ?? 'Abu-Abu')
                            "
                        >
                            {{ task.category?.name }}
                        </Badge>
                    </TableCell>

                    <!-- Notes -->
                    <TableCell v-if="visibleColumns!.notes">
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
        Menampilkan {{ props.tasks.length }} data
    </div>
</template>

<style scoped></style>
