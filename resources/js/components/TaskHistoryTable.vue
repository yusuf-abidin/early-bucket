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
import { inject, ref, Ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import DialogDeleteTask from '@/components/DialogDeleteTask.vue';
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

const props = defineProps<{
    tasks: Task[];
}>();

const dialogDeleteTask = ref(false);
const deleteTask = ref<null | Task>(null);

const handleDelete = (task: Task) => {
    dialogDeleteTask.value = true;
    deleteTask.value = task;
};

const visibleColumns = inject<Ref<Record<string, boolean>>>('visibleColumns');
</script>

<template>
    <div class="rounded-lg border">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[50px]">No</TableHead>
                    <TableHead v-if="visibleColumns!.task">Agenda</TableHead>
                    <TableHead v-if="visibleColumns!.assignedUser"
                        >PIC</TableHead
                    >
                    <TableHead v-if="visibleColumns!.createdDate"
                        >Tanggal Dibuat</TableHead
                    >
                    <TableHead v-if="visibleColumns!.dueDate"
                        >Deadline</TableHead
                    >
                    <TableHead v-if="visibleColumns!.resolvedDate"
                        >Tanggal Selesai</TableHead
                    >
                    <TableHead v-if="visibleColumns!.category"
                        >Kategori</TableHead
                    >
                    <TableHead v-if="visibleColumns!.notes">Notes</TableHead>
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

                    <!-- TANGGAL DIBUAT -->
                    <TableCell v-if="visibleColumns!.createdDate">
                        <Badge variant="secondary">{{ task.created_at }} </Badge>
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
                                task.category?.color?.class ??
                                'bg-gray-50 text-gray-600 inset-ring inset-ring-gray-500/10'
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
                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                <DropdownMenuSeparator />
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
        Menampilkan {{ props.tasks.length }} data
    </div>

    <DialogDeleteTask
        v-model:is-open="dialogDeleteTask"
        v-model:task-data="deleteTask"
    />
</template>

<style scoped></style>
