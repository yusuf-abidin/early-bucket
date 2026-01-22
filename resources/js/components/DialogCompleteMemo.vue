<script setup lang="ts">
import { Memo } from '@/types';
import memos from '@/routes/memos';
import { router } from '@inertiajs/vue3';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { ref } from 'vue';
import { Label } from '@/components/ui/label';

const isOpen = defineModel<boolean>('dialogResolveMemo', { default: false });
const selectedMemo = defineModel<Memo | null>('selectedMemo', {
    default: null,
});

const props = withDefaults(defineProps<{
    mode?: 'index' | 'archive'
}>(), {
    mode: 'index'
});

const showFollowUpNote = ref(false);
const followUpNote = ref('');
const errors = ref<Record<string, string>>({});

const closeModal = () => {
    isOpen.value = false;
    selectedMemo.value = null;
    showFollowUpNote.value = false;
    followUpNote.value = '';
    errors.value = {};
};

const handleResolveMemo = () => {
    errors.value = {};
    const noteValue = followUpNote.value?.trim();
    const updateRoute = memos.update(selectedMemo.value!.id).url;
    const newStatus = selectedMemo.value!.completed_at
        ? null
        : new Date().toISOString().replace('T', ' ').substring(0, 19);

    const data: any = {
        completed_at: newStatus,
    };
    if (noteValue) {
        data.follow_up_note = noteValue;
    }

    router.patch(updateRoute, data, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: (err) => {
            errors.value = err;
        },
    });
};
</script>

<template>
    <AlertDialog v-model:open.lazy="isOpen" @update:open="closeModal">
        <AlertDialogContent class="flex max-h-[90vh] flex-col px-6">
            <AlertDialogHeader>
                <AlertDialogTitle>
                    <template v-if="mode === 'index'">
                        Selesaikan Memo?
                    </template>
                    <template v-else>
                        Pindahkan ke Pending Memo?
                    </template>
                </AlertDialogTitle>
                <AlertDialogDescription>
                    <template v-if="mode === 'index'">
                        Apakah anda yakin ingin menyelesaikan memo ini? Setelah
                        diselesaikan, memo ini akan dipindahkan ke arsip.
                    </template>
                    <template v-else>
                        Apakah anda yakin ingin memindahkan memo ini ke Pending Memo?
                    </template>
                </AlertDialogDescription>
            </AlertDialogHeader>

            <div
                v-if="mode === 'index'"
                class="overflow-y-auto py-2 flex-1 px-1">

            <div v-if="!showFollowUpNote" class="py-2">
                <Button
                    variant="outline"
                    class="w-full"
                    @click="showFollowUpNote = true"
                >
                    Tambahkan Tindak Lanjut
                </Button>
            </div>

            <div v-else class="space-y-2">
                <Label for="follow_up_note" class="text-sm font-medium"
                    >Catatan Tindak Lanjut</Label
                >
                <Textarea
                    id="follow_up_note"
                    name="follow_up_note"
                    v-model="followUpNote"
                    placeholder="Masukkan catatan tindak lanjut..."
                    rows="4"
                    class="w-full max-w-full resize-none overflow-auto break-words"
                    style="width: 100%; max-width: 100%; word-break: break-word"
                />
                <p
                    v-if="errors.follow_up_note"
                    class="text-sm font-medium text-destructive"
                >
                    {{ errors.follow_up_note }}
                </p>
            </div>
            </div>
            <AlertDialogFooter>
                <AlertDialogCancel @click="closeModal"
                    >Cancel</AlertDialogCancel
                >
                <Button @click="handleResolveMemo">
                    <template v-if="mode === 'index'">
                        Selesaikan
                    </template>
                    <template v-else>
                        Pindahkan
                    </template>
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

<style scoped></style>
