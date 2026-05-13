<script setup lang="ts">
import { Article } from '@/types';
import { router } from '@inertiajs/vue3';
import rlqh from '@/routes/rlqh';
import { ref, watch } from 'vue';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogTitle,
    AlertDialogContent, AlertDialogDescription, AlertDialogFooter,
    AlertDialogHeader,
} from '@/components/ui/alert-dialog';
import { buttonVariants } from '@/components/ui/button';

const selectedArticle = defineModel<Article | null>('selectedArticle', {
    default: null,
});

const isOPen = ref(false);

const closeModal = () => {
    selectedArticle.value = null;
    isOPen.value = false;
};

const handleDelete = () => {
    router.delete(rlqh.news.destroy(selectedArticle.value!.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};

watch(
    selectedArticle,
    (newArticle) => {
        if (newArticle) {
            isOPen.value = true;
        } else {
            isOPen.value = false;
        }
    },
    { deep: true },
);
</script>

<template>
    <AlertDialog v-model:open="isOPen" @update:open="closeModal">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Apakah anda yakin?</AlertDialogTitle>
                <AlertDialogDescription>
                    Tindakan ini tidak dapat dibatalkan. News ini akan dihapus secara permanen
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="closeModal">Batal</AlertDialogCancel>
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
