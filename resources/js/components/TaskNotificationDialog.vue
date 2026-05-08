<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Button } from '@/components/ui/button';
import { getBadgeColor } from '@/lib/utils';
import Tasks from '@/routes/tasks';
import { CalendarClock, CalendarCheck2, CheckCircle2, AlertCircle, Clock } from 'lucide-vue-next';
import { computed } from 'vue';
import { Task } from '@/types';

interface Props {
    open: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const page = usePage();

const todayTasks = computed<Task[]>(
    () => (page.props.task_near_overdue as any)?.today ?? [],
);
const tomorrowTasks = computed<Task[]>(
    () => (page.props.task_near_overdue as any)?.tomorrow ?? [],
);

const hasTodayTasks = computed(() => todayTasks.value.length > 0);
const hasTomorrowTasks = computed(() => tomorrowTasks.value.length > 0);
const hasAnyTask = computed(() => hasTodayTasks.value || hasTomorrowTasks.value);

const userName = computed(() => (page.props.auth as any)?.user?.name ?? '');

const handleClose = (val: boolean) => {
    emit('update:open', val);
};

const goToTasks = () => {
    emit('update:open', false);
    router.visit(Tasks.index().url);
};
</script>

<template>
    <Dialog :open="props.open" @update:open="handleClose">
        <DialogContent
            :aria-describedby="undefined"
            class="max-h-[calc(100vh-4rem)] w-full max-w-lg overflow-hidden p-0 sm:max-w-2xl lg:max-w-4xl xl:max-w-5xl"
        >
            <!-- Header -->
            <DialogHeader class="dialog-header px-6 pt-6">
                <div class="flex items-start gap-3">
                    <div class="icon-wrapper mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                        <CalendarClock class="h-5 w-5 text-primary" />
                    </div>
                    <div>
                        <DialogTitle class="text-xl font-semibold leading-snug">
                            Selamat datang, {{ userName }}!
                        </DialogTitle>
                        <DialogDescription id="task-reminder-desc" class="mt-0.5 text-sm text-muted-foreground">
                            <template v-if="hasAnyTask">
                                Berikut pending matter yang perlu diselesaikan segera.
                            </template>
                            <template v-else>
                                Tidak ada pending matter mendesak hari ini.
                            </template>
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div class="separator h-px bg-border" />

            <!-- Body -->
            <ScrollArea class="max-h-[50vh]">
                <div class="space-y-5 px-6 py-2">

                    <!-- All clear state -->
                    <div
                        v-if="!hasAnyTask"
                        class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-border bg-muted/30 py-10 text-center"
                    >
                        <CheckCircle2 class="h-10 w-10 text-emerald-500" />
                        <div>
                            <p class="text-sm font-medium text-foreground">Tidak ada tugas mendekati deadline!</p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Tidak ada tugas dengan deadline hari ini atau besok.
                            </p>
                        </div>
                    </div>

                    <!-- Today's tasks -->
                    <div v-if="hasTodayTasks" class="space-y-2">
                        <div class="flex items-center gap-2">
                            <AlertCircle class="h-4 w-4 text-destructive" />
                            <h3 class="text-sm font-semibold text-destructive">Deadline Hari Ini</h3>
                            <span class="ml-auto rounded-full bg-muted px-2 py-0.5 text-xs font-medium">
                                {{ todayTasks.length }} tugas
                            </span>
                        </div>
                        <ul class="divide-y divide-border rounded-md border bg-card">
                            <li
                                v-for="task in todayTasks"
                                :key="task.id"
                                class="group flex items-start justify-between gap-4 px-4 py-3 transition-colors hover:bg-muted/40"
                            >
                                <p class="text-sm leading-relaxed text-foreground">
                                    {{ task.task_description }}
                                </p>

                                <!-- Category badge -->
                                <span
                                    v-if="task.category?.name"
                                    :class="getBadgeColor(task.category?.color?.name ?? 'Abu-Abu')"
                                    class="shrink-0 inline-flex items-center rounded-full border px-2 py-1 text-[11px] font-medium leading-none"
                                >
                                    {{ task.category.name }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <!-- No today tasks notice (but there are tomorrow tasks) -->
                    <div
                        v-else-if="hasTomorrowTasks"
                        class="flex items-center gap-2 rounded-lg border border-dashed border-border bg-muted/30 px-3 py-2.5"
                    >
                        <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                        <p class="text-xs text-muted-foreground">Tidak ada tugas deadline hari ini.</p>
                    </div>

                    <!-- Tomorrow's tasks -->
                    <div v-if="hasTomorrowTasks" class="space-y-2">
                        <div class="flex items-center gap-2">
                            <Clock class="h-4 w-4 text-amber-500" />
                            <h3 class="text-sm font-semibold text-amber-600 dark:text-amber-400">Deadline Besok</h3>
                            <span class="ml-auto rounded-full bg-muted px-2 py-0.5 text-xs font-medium">
                                {{ tomorrowTasks.length }} tugas
                            </span>
                        </div>
                        <ul class="divide-y divide-border rounded-md border bg-card">
                            <li
                                v-for="task in tomorrowTasks"
                                :key="task.id"
                                class="group flex items-start justify-between gap-4 px-4 py-3 transition-colors hover:bg-muted/40"
                            >
                                <p class="text-sm leading-relaxed text-foreground">
                                    {{ task.task_description }}
                                </p>

                                <!-- Category badge -->
                                <span
                                    v-if="task.category?.name"
                                    :class="getBadgeColor(task.category?.color?.name ?? 'Abu-Abu')"
                                    class="shrink-0 inline-flex items-center rounded-full border px-2 py-1 text-[11px] font-medium leading-none"
                                >
                                    {{ task.category.name }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <!-- No tomorrow tasks notice (but there are today tasks) -->
                    <div
                        v-else-if="hasTodayTasks"
                        class="flex items-center gap-2 rounded-lg border border-dashed border-border bg-muted/30 px-3 py-2.5"
                    >
                        <CalendarCheck2 class="h-4 w-4 shrink-0 text-emerald-500" />
                        <p class="text-xs text-muted-foreground">Tidak ada tugas deadline besok.</p>
                    </div>

                </div>
            </ScrollArea>

            <div class="separator h-px bg-border" />

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 px-6 pb-4">
                <div class="flex gap-2">
                    <Button
                        class="cursor-pointer"
                        variant="ghost"
                        size="sm"
                        @click="handleClose(false)"
                    >
                        Tutup
                    </Button>
                    <Button
                         class="cursor-pointer"
                        v-if="hasAnyTask"
                        size="sm"
                        @click="goToTasks"
                    >
                        Lihat Pending Matter
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
