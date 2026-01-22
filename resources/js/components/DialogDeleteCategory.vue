<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogCancel,
    AlertDialogAction,
} from '@/components/ui/alert-dialog';

defineProps<{
    open: boolean;
    title?: string;
    description?: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
    cancel: [];
}>();
</script>

<template>
    <AlertDialog :open="open" @update:open="emit('update:open', $event)">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{
                    title || 'Are you sure?'
                }}</AlertDialogTitle>
                <AlertDialogDescription>
                    {{ description || 'This action cannot be undone.' }}
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="emit('cancel')"
                    >Cancel</AlertDialogCancel
                >
                <AlertDialogAction @click="emit('confirm')"
                    >Continue</AlertDialogAction
                >
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

<style scoped></style>
