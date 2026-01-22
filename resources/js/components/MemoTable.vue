<script setup lang="ts">
import { Memo } from '@/types';
import { inject, Ref, ref, onMounted, onUnmounted } from 'vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { EllipsisVertical, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { Checkbox } from '@/components/ui/checkbox';

defineProps<{
    memos: Memo[];
}>();

const isOpen = defineModel<boolean>('isOpen', { default: false });
const selectedMemo = defineModel<Memo | null>('selectedMemo', {
    default: null,
});

const deleteIsOpen = defineModel<boolean>('deleteIsOpen', { default: false });

const handleDelete = (memo: Memo) => {
    deleteIsOpen.value = true;
    selectedMemo.value = memo;
};

const openEditModal = (memo: Memo) => {
    selectedMemo.value = memo;
    isOpen.value = true;
};

const visibleColumns = inject<Ref<Record<string, boolean>>>('visibleColumns');

const openLink = (url: string) => {
    if (!url) return;
    const finalUrl = url.startsWith('http') ? url : 'https://' + url;
    window.open(finalUrl, '_blank', 'noopener,noreferrer');
};

const dialogResolveMemo = defineModel<boolean>('dialogResolveMemo', {
    default: false,
});

const handleResolveMemo = (memo: Memo) => {
    dialogResolveMemo.value = true;
    selectedMemo.value = memo;
};

// Resizable columns functionality
const STORAGE_KEY = 'memo-table-column-widths';
const MIN_WIDTH = 80;
const DEFAULT_WIDTHS: Record<string, number> = {
    no: 70,
    received_at: 150,
    origin: 200,
    reference_number: 200,
    subject: 250,
    due_date: 120,
    category: 120,
    document_link: 80,
    assignedUser: 150,
    check: 80,
    follow_up_note: 200,
    actions: 70,
};

const columnWidths = ref<Record<string, number>>({ ...DEFAULT_WIDTHS });
const resizing = ref<string | null>(null);
const startX = ref(0);
const startWidth = ref(0);

// Load saved widths from localStorage
const loadColumnWidths = () => {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            const parsed = JSON.parse(saved);
            columnWidths.value = { ...DEFAULT_WIDTHS, ...parsed };
        }
    } catch (e) {
        console.error('Failed to load column widths:', e);
    }
};

// Save widths to localStorage
const saveColumnWidths = () => {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(columnWidths.value));
    } catch (e) {
        console.error('Failed to save column widths:', e);
    }
};

// Start resizing
const startResize = (e: MouseEvent, column: string) => {
    e.preventDefault();
    resizing.value = column;
    startX.value = e.clientX;
    startWidth.value = columnWidths.value[column] || DEFAULT_WIDTHS[column];
    document.body.style.cursor = 'col-resize';
    document.body.style.userSelect = 'none';
};

// Handle mouse move during resize
const handleMouseMove = (e: MouseEvent) => {
    if (!resizing.value) return;

    const diff = e.clientX - startX.value;
    const newWidth = Math.max(MIN_WIDTH, startWidth.value + diff);
    columnWidths.value[resizing.value] = newWidth;
};

// Stop resizing
const stopResize = () => {
    if (resizing.value) {
        saveColumnWidths();
        resizing.value = null;
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
    }
};

onMounted(() => {
    loadColumnWidths();
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', stopResize);
});

onUnmounted(() => {
    document.removeEventListener('mousemove', handleMouseMove);
    document.removeEventListener('mouseup', stopResize);
});
</script>

<template>
    <div class="rounded-lg border overflow-x-auto">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead
                        class="relative"
                        :style="{ width: columnWidths.no + 'px', minWidth: columnWidths.no + 'px' }"
                    >
                        No
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'no')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.received_at"
                        class="relative"
                        :style="{ width: columnWidths.received_at + 'px', minWidth: columnWidths.received_at + 'px' }"
                    >
                        Tanggal Masuk
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'received_at')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.origin"
                        class="relative"
                        :style="{ width: columnWidths.origin + 'px', minWidth: columnWidths.origin + 'px' }"
                    >
                        Asal
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'origin')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.reference_number"
                        class="relative"
                        :style="{ width: columnWidths.reference_number + 'px', minWidth: columnWidths.reference_number + 'px' }"
                    >
                        Nomor
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'reference_number')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.subject"
                        class="relative"
                        :style="{ width: columnWidths.subject + 'px', minWidth: columnWidths.subject + 'px' }"
                    >
                        Perihal
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'subject')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.due_date"
                        class="relative"
                        :style="{ width: columnWidths.due_date + 'px', minWidth: columnWidths.due_date + 'px' }"
                    >
                        Deadline
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'due_date')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.category"
                        class="relative"
                        :style="{ width: columnWidths.category + 'px', minWidth: columnWidths.category + 'px' }"
                    >
                        Sifat
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'category')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.document_link"
                        class="text-center relative"
                        :style="{ width: columnWidths.document_link + 'px', minWidth: columnWidths.document_link + 'px' }"
                    >
                        Link
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'document_link')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.assignedUser"
                        class="relative"
                        :style="{ width: columnWidths.assignedUser + 'px', minWidth: columnWidths.assignedUser + 'px' }"
                    >
                        PIC
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'assignedUser')"
                        ></div>
                    </TableHead>
                    <TableHead
                        class="text-center relative"
                        v-if="visibleColumns!.check"
                        :style="{ width: columnWidths.check + 'px', minWidth: columnWidths.check + 'px' }"
                    >
                        Done
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'check')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.follow_up_note"
                        class="relative"
                        :style="{ width: columnWidths.follow_up_note + 'px', minWidth: columnWidths.follow_up_note + 'px' }"
                    >
                        Tindak Lanjut
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'follow_up_note')"
                        ></div>
                    </TableHead>
                    <TableHead
                        class="relative"
                        :style="{ width: columnWidths.actions + 'px', minWidth: columnWidths.actions + 'px' }"
                    >
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-if="memos.length === 0">
                    <TableCell
                        :colspan="
                            Object.values(visibleColumns ?? {}).filter(Boolean)
                                .length + 2
                        "
                        class="text-center text-muted-foreground"
                    >
                        No memos found
                    </TableCell>
                </TableRow>
                <TableRow v-for="(memo, index) in memos" :key="memo.id">
                    <TableCell
                        class="text-center font-medium"
                        :style="{ width: columnWidths.no + 'px', minWidth: columnWidths.no + 'px' }"
                    >
                        {{ index + 1 }}
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.received_at"
                        :style="{ width: columnWidths.received_at + 'px', minWidth: columnWidths.received_at + 'px' }"
                    >
                        <Badge variant="secondary">{{ memo.received_at }}</Badge>
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.origin"
                        class="font-medium wrap-break-word whitespace-pre-line"
                        :style="{ width: columnWidths.origin + 'px', minWidth: columnWidths.origin + 'px' }"
                    >
                        {{ memo.origin }}
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.reference_number"
                        class="font-medium wrap-break-word whitespace-pre-line"
                        :style="{ width: columnWidths.reference_number + 'px', minWidth: columnWidths.reference_number + 'px' }"
                    >
                        {{ memo.reference_number }}
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.subject"
                        class="font-medium wrap-break-word whitespace-pre-line"
                        :style="{ width: columnWidths.subject + 'px', minWidth: columnWidths.subject + 'px' }"
                    >
                        {{ memo.subject }}
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.due_date"
                        :style="{ width: columnWidths.due_date + 'px', minWidth: columnWidths.due_date + 'px' }"
                    >
                        <Badge variant="outline">{{ memo.due_date }}</Badge>
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.category"
                        :style="{ width: columnWidths.category + 'px', minWidth: columnWidths.category + 'px' }"
                    >
                        <Badge
                            :class="
                                memo.category?.color?.class ??
                                'bg-gray-50 text-gray-600 inset-ring inset-ring-gray-500/10'
                            "
                        >
                            {{ memo.category?.name }}
                        </Badge>
                    </TableCell>

                    <TableCell
                        class="text-center"
                        v-if="visibleColumns!.document_link"
                        :style="{ width: columnWidths.document_link + 'px', minWidth: columnWidths.document_link + 'px' }"
                    >
                        <template v-if="memo.document_link">
                            <Button
                                @click="openLink(memo.document_link)"
                                class="cursor-pointer text-gray-600"
                                type="button"
                                variant="ghost"
                                size="icon"
                                title="Open memo link in new tab"
                            >
                                <Eye class="h-4 w-4" />
                            </Button>
                        </template>
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.assignedUser"
                        :style="{ width: columnWidths.assignedUser + 'px', minWidth: columnWidths.assignedUser + 'px' }"
                    >
                        <div class="flex flex-col flex-wrap gap-1">
                            <Badge
                                v-for="user in memo.users"
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

                    <TableCell
                        v-if="visibleColumns!.check"
                        class="text-center"
                        :style="{ width: columnWidths.check + 'px', minWidth: columnWidths.check + 'px' }"
                    >
                        <Checkbox
                            :model-value="!!memo.completed_at"
                            @click="handleResolveMemo(memo)"
                        />
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.follow_up_note"
                        class="font-medium wrap-break-word whitespace-pre-line"
                        :style="{ width: columnWidths.follow_up_note + 'px', minWidth: columnWidths.follow_up_note + 'px' }"
                    >
                        {{ memo.follow_up_note }}
                    </TableCell>

                    <TableCell
                        :style="{ width: columnWidths.actions + 'px', minWidth: columnWidths.actions + 'px' }"
                    >
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
                                <DropdownMenuItem @click="openEditModal(memo)">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    Update
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    @click="handleDelete(memo)"
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
</template>

<style scoped>
.resize-handle {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 8px;
    cursor: col-resize;
    user-select: none;
    touch-action: none;
    z-index: 1;
}

.resize-handle:hover {
    background-color: rgba(59, 130, 246, 0.3);
}

.resize-handle:active {
    background-color: rgba(59, 130, 246, 0.5);
}

/* Optional: Add visual feedback during resize */
:deep(th) {
    position: relative;
}
</style>
