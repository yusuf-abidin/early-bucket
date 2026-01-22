<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Task } from '@/types';
import { router } from '@inertiajs/vue3';
import tasks from '@/routes/tasks';
import { buttonVariants } from '@/components/ui/button';

const isOpen = defineModel<boolean>('isOpen', { default: false });

const taskData = defineModel<Task | null>('taskData', { default: null });

const closeModal = () => {
    isOpen.value = false;
    taskData.value = null;
};

const handleDelete = () => {
    router.delete(tasks.destroy(taskData.value!.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};
</script>

<template>
    <AlertDialog v-model:open="isOpen" @update:open="closeModal">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle> Apakah Anda yakin? </AlertDialogTitle>
                <AlertDialogDescription>
                    Tindakan ini tidak dapat dibatalkan. Agenda pending matter
                    ini akan dihapus secara permanen beserta seluruh data tugas
                    terkait
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
