<script setup lang="ts">
import { Memo } from '@/types';
import { router } from '@inertiajs/vue3';
import memos from '@/routes/memos';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogFooter,
    AlertDialogDescription,
    AlertDialogAction,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { buttonVariants } from '@/components/ui/button';

const deleteIsOpen = defineModel<boolean>('deleteIsOpen', { default: false });

const selectedMemo = defineModel<Memo | null>('selectedMemo', {
    default: null,
});

const closeModal = () => {
    deleteIsOpen.value = false;
    selectedMemo.value = null;
};

const handleDelete = () => {
    router.delete(memos.destroy(selectedMemo.value!.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};
</script>

<template>
    <AlertDialog v-model:open="deleteIsOpen" @update:open="closeModal">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Apakah Anda yakin?</AlertDialogTitle>
                <AlertDialogDescription>
                    Tindakan ini tidak dapat dibatalkan. Memo/Surat ini akan
                    dihapus secara permanen
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="closeModal">Batal</AlertDialogCancel>
                <AlertDialogAction
                    :class="buttonVariants({ variant: 'destructive' })"
                    @click="handleDelete"
                    >Hapus</AlertDialogAction
                >
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

<style scoped></style>
