<script setup lang="ts">
import { Memo } from '@/types';
import { inject, Ref } from 'vue';
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
import { useTableResize } from '@/composables/useTableResize';
import '@/assets/styles/table-resize.css';

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

const { columnWidths, startResize } = useTableResize({
    storageKey: 'memo-table-column-widths',
    defaultWidths: {
        no: 20,
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
    },
    minWidth: 20,
});
</script>

<template>
    <div class="overflow-x-auto rounded-lg border">
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
                        v-if="visibleColumns!.received_at"
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.received_at + 'px',
                            minWidth: columnWidths.received_at + 'px',
                        }"
                    >
                        Tanggal Masuk
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'received_at')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.origin"
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.origin + 'px',
                            minWidth: columnWidths.origin + 'px',
                        }"
                    >
                        Asal
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'origin')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.reference_number"
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.reference_number + 'px',
                            minWidth: columnWidths.reference_number + 'px',
                        }"
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
                        v-if="visibleColumns!.subject"
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.subject + 'px',
                            minWidth: columnWidths.subject + 'px',
                        }"
                    >
                        Perihal
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'subject')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.due_date"
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.due_date + 'px',
                            minWidth: columnWidths.due_date + 'px',
                        }"
                    >
                        Deadline
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'due_date')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.category"
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.category + 'px',
                            minWidth: columnWidths.category + 'px',
                        }"
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
                    >
                        Link
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'document_link')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.assignedUser"
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.assignedUser + 'px',
                            minWidth: columnWidths.assignedUser + 'px',
                        }"
                    >
                        PIC
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'assignedUser')"
                        ></div>
                    </TableHead>
                    <TableHead
                        class="relative text-center font-bold"
                        v-if="visibleColumns!.check"
                        :style="{
                            width: columnWidths.check + 'px',
                            minWidth: columnWidths.check + 'px',
                        }"
                    >
                        Done
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'check')"
                        ></div>
                    </TableHead>
                    <TableHead
                        v-if="visibleColumns!.follow_up_note"
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.follow_up_note + 'px',
                            minWidth: columnWidths.follow_up_note + 'px',
                        }"
                    >
                        Tindak Lanjut
                        <div
                            class="resize-handle"
                            @mousedown="(e) => startResize(e, 'follow_up_note')"
                        ></div>
                    </TableHead>
                    <TableHead
                        class="relative font-bold"
                        :style="{
                            width: columnWidths.actions + 'px',
                            minWidth: columnWidths.actions + 'px',
                        }"
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
                        :style="{
                            width: columnWidths.no + 'px',
                            minWidth: columnWidths.no + 'px',
                        }"
                    >
                        {{ index + 1 }}
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.received_at"
                        :style="{
                            width: columnWidths.received_at + 'px',
                            minWidth: columnWidths.received_at + 'px',
                        }"
                    >
                        <Badge variant="secondary">{{
                            memo.received_at
                        }}</Badge>
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.origin"
                        class="font-medium wrap-break-word whitespace-pre-line"
                        :style="{
                            width: columnWidths.origin + 'px',
                            minWidth: columnWidths.origin + 'px',
                        }"
                    >
                        {{ memo.origin }}
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.reference_number"
                        class="font-medium wrap-break-word whitespace-pre-line"
                        :style="{
                            width: columnWidths.reference_number + 'px',
                            minWidth: columnWidths.reference_number + 'px',
                        }"
                    >
                        {{ memo.reference_number }}
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.subject"
                        class="font-medium wrap-break-word whitespace-pre-line"
                        :style="{
                            width: columnWidths.subject + 'px',
                            minWidth: columnWidths.subject + 'px',
                        }"
                    >
                        {{ memo.subject }}
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.due_date"
                        :style="{
                            width: columnWidths.due_date + 'px',
                            minWidth: columnWidths.due_date + 'px',
                        }"
                    >
                        <Badge variant="outline">{{ memo.due_date }}</Badge>
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.category"
                        :style="{
                            width: columnWidths.category + 'px',
                            minWidth: columnWidths.category + 'px',
                        }"
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
                        :style="{
                            width: columnWidths.document_link + 'px',
                            minWidth: columnWidths.document_link + 'px',
                        }"
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
                        :style="{
                            width: columnWidths.assignedUser + 'px',
                            minWidth: columnWidths.assignedUser + 'px',
                        }"
                    >
                        <div class="flex flex-row flex-wrap gap-1">
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
                        :style="{
                            width: columnWidths.check + 'px',
                            minWidth: columnWidths.check + 'px',
                        }"
                    >
                        <Checkbox
                            :model-value="!!memo.completed_at"
                            @click="handleResolveMemo(memo)"
                        />
                    </TableCell>

                    <TableCell
                        v-if="visibleColumns!.follow_up_note"
                        class="font-medium wrap-break-word whitespace-pre-line"
                        :style="{
                            width: columnWidths.follow_up_note + 'px',
                            minWidth: columnWidths.follow_up_note + 'px',
                        }"
                    >
                        {{ memo.follow_up_note }}
                    </TableCell>

                    <TableCell
                        :style="{
                            width: columnWidths.actions + 'px',
                            minWidth: columnWidths.actions + 'px',
                        }"
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
                                <DropdownMenuLabel>Aksi</DropdownMenuLabel>
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
                                    Hapus
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>

<style scoped></style>
