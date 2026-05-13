<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { UserSummary } from '@/types';
import { router } from '@inertiajs/vue3';
import tasks from '@/routes/tasks';
import { Pie } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement } from 'chart.js';
import debtorSavings from '@/routes/debtor-savings';
import rlqh from '@/routes/rlqh';

ChartJS.register(Title, Tooltip, Legend, ArcElement);

const props = defineProps<{
    users_summary: UserSummary[];
    mode?: 'pending' | 'history' | 'debtor_saving';
    scope?: string;
}>();

const mode = props.mode ?? 'pending';
const scope = props.scope ?? 'central';

// Helper untuk pending mode
const hasTasks = (user: UserSummary) => {
    return (
        user.pending_count > 0 ||
        user.near_overdue_count > 0 ||
        user.overdue_count > 0 ||
        (user.completed_count ?? 0) > 0
    );
};

// Helper untuk history mode
const hasHistory = (user: UserSummary) => {
    return (user.completed_this_week_count ?? 0) > 0;
};

const getChartData = (user: UserSummary) => {
    if (mode === 'pending' || mode === 'debtor_saving') {
        const total =
            user.pending_count +
            user.near_overdue_count +
            user.overdue_count +
            (user.completed_count ?? 0);
        return {
            labels: [
                'Pending',
                'Mendekati Deadline',
                'Melewati Deadline',
                'Selesai',
            ],
            datasets: [
                {
                    data: [
                        user.pending_count,
                        user.near_overdue_count,
                        user.overdue_count,
                        user.completed_count,
                    ],
                    backgroundColor:
                        total > 0
                            ? ['#9CA3AF', '#FBBF24', '#EF4444', '#2B7FFF']
                            : [
                                  'transparent',
                                  'transparent',
                                  'transparent',
                                  'transparent',
                              ],
                    hoverOffset: 2,
                    borderWidth: 0,
                },
            ],
        };
    } else if (mode === 'history') {
        return {
            labels: ['Tugas Selesai'],
            datasets: [
                {
                    data: [user.completed_this_week_count ?? 0],
                    backgroundColor: ['#14B8A6'], // Teal/Green for completed
                    hoverOffset: 4,
                },
            ],
        };
    }
    return { labels: [], datasets: [] };
};

const getTotalTasks = (user: UserSummary) => {
    if (mode === 'pending' || mode === 'debtor_saving') {
        return (
            user.pending_count +
            user.near_overdue_count +
            user.overdue_count +
            (user.completed_count ?? 0)
        );
    } else if (mode === 'history') {
        return user.completed_this_week_count ?? 0;
    }
    return 0;
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '70%', // Membuat doughnut dengan lubang tengah yang cukup besar untuk avatar
    plugins: {
        legend: {
            display: false, // Hide legend to keep it clean
        },
        tooltip: {
            position: 'nearest', // Membantu tooltip muncul di posisi terbaik tanpa terpotong
            callbacks: {
                label: (context) => {
                    return `${context.label}: ${context.raw}`;
                },
            },
            // Untuk menghindari potong, tambahkan padding atau external jika perlu, tapi ini cukup untuk sekarang
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            padding: 10,
        },
    },
    elements: {
        arc: {
            borderWidth: 0,
        },
    },
};

const handleFilter = (userId: number) => {
    const currentUserId = new URLSearchParams(window.location.search).get(
        'user_id',
    );
    const query = currentUserId == userId.toString() ? {} : { user_id: userId };

    let url = null;
    if (mode === 'pending') {
        if (scope === 'rlqh'){
            url = rlqh.tasks.index().url;
        }else {
            url = tasks.index().url;
        }
    } else if (mode === 'history') {
        if (scope === 'rlqh'){
            url = rlqh.tasks.history().url;
        }else {
            url = tasks.history().url;
        }
    } else if (mode === 'debtor_saving') {
        url = debtorSavings.index().url;
    }

    router.get(url!, query, {
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <div class="w-full overflow-hidden">
        <ScrollArea class="w-full">
            <div class="flex min-w-max justify-center gap-4 px-4 pb-4">
                <div
                    v-for="user in props.users_summary"
                    :key="user.id"
                    class="flex flex-col items-center transition-all hover:scale-105"
                >
                    <div
                        @click="handleFilter(user.id)"
                        class="flex h-full max-w-xs min-w-52 cursor-pointer flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg transition-all hover:shadow-xl dark:border-gray-700 dark:bg-gray-800"
                    >
                        <!-- Header dengan warna corporate solid -->
                        <div
                            class="bg-gradient-to-r from-blue-600 to-blue-700 p-4 pb-12"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="h-2 w-2 rounded-full bg-white/80"
                                    ></div>
                                    <div
                                        class="h-2 w-2 rounded-full bg-white/60"
                                    ></div>
                                    <div
                                        class="h-2 w-2 rounded-full bg-white/40"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Chart Container dengan Avatar -->
                        <div class="relative -mt-14 flex justify-center px-4">
                            <div
                                class="relative flex h-32 w-full justify-center"
                            >
                                <template
                                    v-if="
                                        ((mode === 'pending' ||
                                            mode === 'debtor_saving') &&
                                            hasTasks(user)) ||
                                        (mode === 'history' && hasHistory(user))
                                    "
                                >
                                    <Pie
                                        :data="getChartData(user)"
                                        :options="chartOptions"
                                        class="relative z-10 h-32 w-32"
                                    />
                                    <Avatar
                                        class="absolute top-1/2 left-1/2 z-0 h-20 w-20 -translate-x-1/2 -translate-y-1/2 shadow-xl ring-4 ring-white dark:ring-gray-800"
                                    >
                                        <AvatarImage
                                            v-if="user.avatar"
                                            :src="`/storage/${user.avatar}`"
                                            :alt="user.name"
                                        />
                                        <AvatarFallback
                                            class="bg-blue-100 font-bold text-blue-700"
                                        >
                                            {{
                                                user.name
                                                    .split(' ')
                                                    .map((n) => n[0])
                                                    .join('')
                                                    .toUpperCase()
                                            }}
                                        </AvatarFallback>
                                    </Avatar>
                                </template>
                                <template v-else>
                                    <Avatar
                                        class="absolute top-1/2 left-1/2 z-0 h-24 w-24 -translate-x-1/2 -translate-y-1/2 shadow-xl ring-4 ring-white dark:ring-gray-800"
                                    >
                                        <AvatarImage
                                            v-if="user.avatar"
                                            :src="`/storage/${user.avatar}`"
                                            :alt="user.name"
                                        />
                                        <AvatarFallback
                                            class="bg-blue-100 font-bold text-blue-700"
                                        >
                                            {{
                                                user.name
                                                    .split(' ')
                                                    .map((n) => n[0])
                                                    .join('')
                                                    .toUpperCase()
                                            }}
                                        </AvatarFallback>
                                    </Avatar>
                                </template>
                            </div>
                        </div>

                        <!-- Info Section -->
                        <div class="mt-auto w-full p-4 pt-2 text-center">
                            <p
                                class="truncate text-base font-bold text-gray-900 dark:text-gray-100"
                            >
                                {{ user.name }}
                            </p>
                            <p
                                class="truncate text-xs font-medium text-blue-600 dark:text-blue-400"
                            >
                                {{ user.position }}
                            </p>
                            <div
                                class="mt-2 flex items-center justify-center gap-2 rounded-lg bg-blue-50 px-2 py-1 dark:bg-blue-900/20"
                            >
                                <span
                                    class="text-xs text-gray-600 dark:text-gray-400"
                                    >Total:</span
                                >
                                <span
                                    class="text-sm font-bold text-blue-600 dark:text-blue-400"
                                >
                                    {{ getTotalTasks(user) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <ScrollBar orientation="horizontal" />
        </ScrollArea>
    </div>
</template>
