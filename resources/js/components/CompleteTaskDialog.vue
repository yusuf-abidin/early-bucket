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
import debtorSavings from '@/routes/debtor-savings';

const props = withDefaults(
    defineProps<{
        mode?: 'pending_matter' | 'debtor_savings';
    }>(),
    {
        mode: 'pending_matter',
    },
);

const isOpen = defineModel<boolean>('completeTaskIsOpen', { default: false });
const taskData = defineModel<Task | null>('selectedData', { default: null });

const closeModal = () => {
    isOpen.value = false;
    taskData.value = null;
};

const handleResolveTask = () => {
    let updateRoute = null;
    if (props.mode === 'pending_matter') {
        updateRoute = tasks.update(taskData.value!.id).url;
    }else {
        updateRoute = debtorSavings.update(taskData.value!.id).url
    }
    console.log(updateRoute);

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
                <AlertDialogTitle>
                    <template v-if="taskData?.completed_at">
                        Batalkan Penyelesaian Agenda
                    </template>
                    <template v-else>
                        Selesaikan Agenda?
                    </template>
                </AlertDialogTitle>
                <AlertDialogDescription>
                    <template v-if="mode === 'pending_matter'">
                        Apakah Anda yakin ingin menyelesaikan agenda ini? Setelah
                        diselesaikan, agenda akan dipindahkan ke riwayat pekerjaan.
                    </template>
                    <template v-else>
                        Apakah anda yakin?
                    </template>
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
