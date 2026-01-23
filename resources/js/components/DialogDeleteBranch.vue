<script setup lang="ts">
import { Branch } from '@/types';
import { router } from '@inertiajs/vue3';
import branches from '@/routes/admin/branches';
import { AlertDialog,
    AlertDialogAction,
    AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { buttonVariants } from '@/components/ui/button';

const dialogDeleteBranch = defineModel<boolean>('dialogDeleteBranch', {
    default: false,
});

const selectedBranch = defineModel<Branch | null>('selectedBranch', {
    default: null,
});

const closeModal = () => {
    dialogDeleteBranch.value = false;
    selectedBranch.value = null
}

const handleDelete = () => {
    router.delete(branches.destroy(selectedBranch.value!.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        }
    });
}

</script>

<template>
    <AlertDialog v-model:open="dialogDeleteBranch" @update:open="closeModal">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Apakah anda yakin?</AlertDialogTitle>
                <AlertDialogDescription>
                    Tindakan ini tidak dapat dibatalkan. Cabang ini akan dihapus secara permanen beserta data yang bersangkutan
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="closeModal">
                    Batal
                </AlertDialogCancel>
                <AlertDialogAction
                    :class="buttonVariants({ variant: 'destructive'})"
                    @click="handleDelete"
                >
                    Hapus
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>

</template>

<style scoped></style>
