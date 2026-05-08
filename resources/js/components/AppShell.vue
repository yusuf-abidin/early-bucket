<script setup lang="ts">
import TaskReminderDialog from '@/components/TaskNotificationDialog.vue';
import { SidebarProvider } from '@/components/ui/sidebar';
import { router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const popupTaskIsOpen = ref(false);
const isOpen = usePage().props.sidebarOpen;
const errors = computed(() => usePage().props.errors);
const page = usePage();

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            nextTick(() => {
                toast.success(flash.success!, {
                    position: 'top-right',
                });
            });
        }
        if (flash?.error) {
            nextTick(() => {
                toast.error(flash.error!, {
                    position: 'top-right',
                });
            });
        }
        if (errors.value.message) {
            toast.error(errors.value.message, {
                position: 'top-right',
            });
        }
    },
    { deep: true, immediate: true },
);

onMounted(() => {
    const today = new Date().toLocaleDateString('en-CA', {
        timeZone: 'Asia/Jakarta',
    });
    const lastNotif = localStorage.getItem('last_notif_date');

    if (lastNotif !== today) {
        router.reload({
            only: ['task_near_overdue'],
            onSuccess: () => {
                popupTaskIsOpen.value = true;
                localStorage.setItem('last_notif_date', today);
            },
        });
    }
});
</script>

<template>
    <TaskReminderDialog v-model:open="popupTaskIsOpen" />

    <Toaster richColors :closeButton="true" />

    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
    </SidebarProvider>
</template>
