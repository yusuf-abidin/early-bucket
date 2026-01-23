<script setup lang="ts">

import { Area } from '@/types';
import { router } from '@inertiajs/vue3';
import areas from '@/routes/admin/areas';
import { AlertDialog,
    AlertDialogAction,
    AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { buttonVariants } from '@/components/ui/button';

const dialogDeleteArea = defineModel<boolean>('dialogDeleteArea', { default: false });
const selectedArea = defineModel<Area | null>('selectedArea', {
    default: null,
});

const closeModal = () => {
    dialogDeleteArea.value = false;
    selectedArea.value = null;
}

const handleDelete = () => {
    router.delete(areas.destroy(selectedArea.value!.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
        },
    });
}


</script>

<template>
    <AlertDialog v-model:open="dialogDeleteArea" @update:open="closeModal">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Apakah anda yakin?</AlertDialogTitle>
                <AlertDialogDescription>
                    Tindakan ini tidak dapat dibatalkan. Area ini akan dihapus secara permanen dan seluruh data cabang pada area ini.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="closeModal">Batal</AlertDialogCancel>
                <AlertDialogAction
                    :class="buttonVariants({ variant: 'destructive' })"
                    @click="handleDelete"
                >
                    Hapus
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

<style scoped></style>
