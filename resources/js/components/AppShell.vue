<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';
import 'vue-sonner/style.css';
import { computed, nextTick, watch } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

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
// watchEffect(() => {
//     console.log(flash.value)
//     if (flash.value.success) {
//         toast.success(flash.value.success, {
//             position: 'top-right',
//         });
//     } else if (flash.value.error) {
//         toast.error(flash.value.error, {
//             position: 'top-right',
//         });
//     }
//
//     if (errors.value.message) {
//         toast.error(errors.value.message, {
//             position: 'top-right',
//         });
//     }
// });
</script>

<template>
    <Toaster richColors :closeButton="true" />
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
    </SidebarProvider>
</template>
