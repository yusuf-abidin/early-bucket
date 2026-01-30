<script setup lang="ts">
import { Task } from '@/types';
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
import tasks from '@/routes/tasks';
import { router } from '@inertiajs/vue3';

const isOpen = defineModel<boolean>('isOpen', { default: false });
const taskData = defineModel<Task | null>('taskData', { default: null });

const closeModal = () => {
    isOpen.value = false;
    taskData.value = null;
};

const handleResolveTask = () => {
    const updateRoute = tasks.update(taskData.value!.id).url;

    // Tentukan nilai baru: jika sudah ada, jadikan null. Jika belum, beri timestamp.
    const newStatus = taskData.value!.completed_at
        ? null
        : new Date().toISOString().replace('T', ' ').substring(0, 19);

    router.patch(
        updateRoute,
        {
            completed_at: newStatus,
        },
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <AlertDialog v-model:open="isOpen" @update:open="closeModal">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle> Selesaikan Tugas? </AlertDialogTitle>
                <AlertDialogDescription>
                    Apakah Anda yakin ingin menyelesaikan agenda ini? Setelah
                    diselesaikan, agenda akan dipindahkan ke riwayat pekerjaan.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Batal</AlertDialogCancel>
                <AlertDialogAction @click="handleResolveTask"
                    >Selesaikan</AlertDialogAction
                >
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

<style scoped></style>
