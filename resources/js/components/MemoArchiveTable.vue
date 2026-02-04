<script setup lang="ts">
import { Memo } from '@/types';
import {
    Table,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { inject, Ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Eye, EllipsisVertical, MoveUpLeft, Trash2 } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTableResize } from '@/composables/useTableResize';
import '@/assets/styles/table-resize.css';

const props = defineProps<{
    memos: Memo[];
}>();

const deleteIsOpen = defineModel<boolean>('deleteIsOpen', {
    default: false,
});

const selectedMemo = defineModel<Memo | null>('selectedMemo', {
    default: null,
});

const dialogResolveMemo = defineModel<boolean>('dialogResolveMemo', {
    default: false,
});

const visibleColumns = inject<Ref<Record<string, boolean>>>('visibleColumns');

const openLink = (url: string) => {
    if (!url) return;
    const finalUrl = url.startsWith('http') ? url : 'https://' + url;
    window.open(finalUrl, '_blank', 'noopener,noreferrer');
};

const handleDelete = (memo: Memo) => {
    deleteIsOpen.value = true;
    selectedMemo.value = memo;
};

const handleResolveMemo = (memo: Memo) => {
    dialogResolveMemo.value = true;
    selectedMemo.value = memo;
};
const { columnWidths, startResize } = useTableResize({
    storageKey: 'memo-archive-table-column-widths',
    defaultWidths: {
        no: 20,
        received_at: 150,
        origin: 200,
        reference_number: 200,
        subject: 250,
        category: 120,
        document_link: 80,
        due_date: 120,
        completed_at: 120,
        assignedUser: 150,
        follow_up_note: 200,
        actions: 70,
    },
    minWidth: 20,
});
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
                            width: columnWidths.received_at + 'px',
                            minWidth: columnWidths.received_at + 'px',
                        }"
                        v-if="visibleColumns!.received_at"
                    >
                        Tanggal Masuk
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'received_at')"
                        ></div>
                    </TableHead>
                    <TableHead
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.origin + 'px',
                            minWidth: columnWidths.origin + 'px',
                        }"
                        v-if="visibleColumns!.origin"
                    >
                        Asal
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'origin')"
                        ></div>
                    </TableHead>
                    <TableHead
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.reference_number + 'px',
                            minWidth: columnWidths.reference_number + 'px',
                        }"
                        v-if="visibleColumns!.reference_number"
                    >
                        Nomor
                        <div
                            class="resize-handle"
                            @mousedown="
                                (e) => startResize(e, 'reference_number')
                            "
                        ></div>
                    </TableHead>
                    <TableHead
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.subject + 'px',
                            minWidth: columnWidths.subject + 'px',
                        }"
                        v-if="visibleColumns!.subject"
                    >
                        Perihal
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'subject')"
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
                        Sifat
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'category')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.document_link"
                        class="relative text-center font-bold"
                        :style="{
                            width: columnWidths.document_link + 'px',
                            minWidth: columnWidths.document_link + 'px',
                        }"
                        >Link
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'document_link')"
                        ></div>
                    </TableHead>
                    <TableHead
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.due_date + 'px',
                            minWidth: columnWidths.due_date + 'px',
                        }"
                        v-if="visibleColumns!.due_date"
                    >
                        Deadline
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'due_date')"
                        ></div>
                    </TableHead>
                    <TableHead
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.completed_at + 'px',
                            minWidth: columnWidths.completed_at + 'px',
                        }"
                        v-if="visibleColumns!.completed_at"
                    >
                        Tanggal Selesai
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'completed_at')"
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
                            width: columnWidths.follow_up_note + 'px',
                            minWidth: columnWidths.follow_up_note + 'px',
                        }"
                        v-if="visibleColumns!.follow_up_note"
                    >
                        Tindak Lanjut
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'follow_up_note')"
                        ></div>
                    </TableHead>
                    <TableHead class="w-[50px]"></TableHead>
                </TableRow>
            </TableHeader>
            <TableRow v-if="props.memos.length === 0">
                <TableCell
                    :colspan="
                        Object.values(visibleColumns ?? {}).filter(Boolean)
                            .length + 1
                    "
                    class="text-center text-muted-foreground"
                >
                    Data memo tidak ditemukan
                </TableCell>
            </TableRow>
            <TableRow v-for="(memo, index) in props.memos" :key="memo.id">
                <!-- No -->
                <TableCell class="text-center font-medium">
                    {{ index + 1 }}
                </TableCell>

                <!-- TANGGAL MASUK -->
                <TableCell v-if="visibleColumns!.received_at">
                    <Badge variant="secondary">
                        {{ memo.received_at }}
                    </Badge>
                </TableCell>

                <!-- ASAL -->
                <TableCell
                    v-if="visibleColumns!.origin"
                    class="max-w-[350px] min-w-[100px] font-medium wrap-break-word whitespace-pre-line"
                >
                    {{ memo.origin }}
                </TableCell>

                <!-- NOMOR -->
                <TableCell
                    v-if="visibleColumns!.reference_number"
                    class="max-w-[350px] min-w-[100px] font-medium wrap-break-word whitespace-pre-line"
                >
                    {{ memo.reference_number }}
                </TableCell>

                <!-- PERIHAL -->
                <TableCell
                    v-if="visibleColumns!.subject"
                    class="max-w-[350px] min-w-[100px] font-medium wrap-break-word whitespace-pre-line"
                >
                    {{ memo.subject }}
                </TableCell>

                <!-- KATEGORI -->
                <TableCell v-if="visibleColumns!.category">
                    <Badge
                        :class="
                            memo.category?.color?.class ??
                            'bg-gray-50 text-gray-600 inset-ring inset-ring-gray-500/10'
                        "
                    >
                        {{ memo.category.name }}
                    </Badge>
                </TableCell>

                <!-- DOCUMENT LINK -->
                <TableCell
                    class="text-center"
                    v-if="visibleColumns!.document_link"
                >
                    <template v-if="memo.document_link">
                        <Button
                            @click="openLink(memo.document_link)"
                            class="cursor-pointer text-gray-600"
                            type="button"
                            variant="ghost"
                            size="icon"
                            title="Open memo in new tab"
                        >
                            <Eye class="h-2 w-2" />
                        </Button>
                    </template>
                    <template v-else>
                        <span>-</span>
                    </template>
                </TableCell>

                <!-- DEADLINE -->
                <TableCell v-if="visibleColumns!.due_date">
                    <Badge variant="secondary">
                        {{ memo.due_date }}
                    </Badge>
                </TableCell>

                <!-- TANGGAL SELESAI -->
                <TableCell v-if="visibleColumns!.completed_at">
                    <Badge>
                        {{ memo.completed_at }}
                    </Badge>
                </TableCell>

                <!-- PIC -->
                <TableCell v-if="visibleColumns!.assignedUser">
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

                <!-- TINDAK LANJUT -->
                <TableCell
                    v-if="visibleColumns!.follow_up_note"
                    class="max-w-[350px] min-w-[100px] font-medium wrap-break-word whitespace-pre-line"
                >
                    {{ memo.follow_up_note }}
                </TableCell>

                <!-- ACTION -->
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
                            <DropdownMenuItem @click="handleResolveMemo(memo)">
                                <MoveUpLeft class="mr-2 h-4 w-4" />
                                Pindah ke pending memo
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
        </Table>
    </div>
</template>

<style scoped></style>
