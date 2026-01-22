<script setup lang="ts">
import { User } from '@/types';
import { router } from '@inertiajs/vue3';
import admin from '@/routes/admin';
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
import { buttonVariants } from '@/components/ui/button';

const isOpen = defineModel<boolean>('isOpen', { default: false });
const userData = defineModel<User | null>('userData', { default: null });

const closeModal = () => {
    isOpen.value = false;
    userData.value = null;
};

const handleDelete = () => {
    router.delete(admin.users.destroy(userData.value!.id).url, {
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
                <AlertDialogTitle> Hapus Data Pengguna? </AlertDialogTitle>
                <AlertDialogDescription>
                    Data pengguna yang telah dihapus tidak dapat dipulihkan
                    kembali. Seluruh informasi dan riwayat aktivitas pengguna
                    ini akan hilang secara permanen.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Batal</AlertDialogCancel>
                <AlertDialogAction :class="buttonVariants({variant: 'destructive'})" @click="handleDelete"
                    >Hapus</AlertDialogAction
                >
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

<style scoped></style>
