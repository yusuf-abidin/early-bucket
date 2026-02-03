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
import debtorSavings from '@/routes/debtor-savings';

const props = withDefaults(
    defineProps<{
        mode?: 'pending_matter' | 'debtor_savings';
    }>(),
    {
        mode: 'pending_matter',
    },
);

const isOpen = defineModel<boolean>('dialogDeleteTaskIsOpen', {
    default: false,
});

const taskData = defineModel<Task | null>('selectedData', { default: null });

const closeModal = () => {
    isOpen.value = false;
    taskData.value = null;
};

const handleDelete = () => {
    let deleteUrl= null
    if (props.mode === 'pending_matter') {
        deleteUrl = tasks.destroy(taskData.value!.id).url
    }else {
        deleteUrl = debtorSavings.destroy(taskData.value!.id).url
    }
    router.delete(deleteUrl, {
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
                    <template v-if="props.mode === 'pending_matter'">
                        Tindakan ini tidak dapat dibatalkan. Agenda pending
                        matter ini akan dihapus secara permanen beserta seluruh
                        data tugas terkait
                    </template>
                    <template v-else>
                        Tindakan ini tidak dapat dibatalkan. Data akan dihapus secara permanen
                    </template>
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
