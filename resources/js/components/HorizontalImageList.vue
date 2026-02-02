<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { UserSummary } from '@/types';
import { router } from '@inertiajs/vue3';
import tasks from '@/routes/tasks';
import { Pie } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, ArcElement);

const props = defineProps<{
    users_summary: UserSummary[];
    mode?: 'pending' | 'history';
}>();

const mode = props.mode ?? 'pending';

// Helper untuk pending mode
const hasTasks = (user: UserSummary) => {
    return (
        user.pending_count > 0 ||
        user.near_overdue_count > 0 ||
        user.overdue_count > 0
    );
};

// Helper untuk history mode
const hasHistory = (user: UserSummary) => {
    return (user.completed_this_week_count ?? 0) > 0;
};

const getChartData = (user: UserSummary) => {
    if (mode === 'pending') {
        const total =
            user.pending_count + user.near_overdue_count + user.overdue_count;
        return {
            labels: ['Pending', 'Mendekati Deadline', 'Melewati Deadline'],
            datasets: [
                {
                    data: [
                        user.pending_count,
                        user.near_overdue_count,
                        user.overdue_count,
                    ],
                    backgroundColor:
                        total > 0
                            ? ['#9CA3AF', '#FBBF24', '#EF4444']
                            : ['transparent', 'transparent', 'transparent'],
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
    if (mode === 'pending') {
        return (
            user.pending_count + user.near_overdue_count + user.overdue_count
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

    const url = mode === 'pending' ? tasks.index().url : tasks.history().url;

    router.get(url, query, {
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
                        class="flex h-full max-w-xs min-w-52 cursor-pointer flex-col items-center rounded-xl border border-border bg-card p-2 text-card-foreground shadow-md"
                    >
                        <!-- Chart Container dengan Avatar di Tengah -->
                        <div
                            class="relative flex h-32 w-full justify-center"
                        >
                            <template
                                v-if="
                                    (mode === 'pending' && hasTasks(user)) ||
                                    (mode === 'history' && hasHistory(user))
                                "
                            >
                                <Pie
                                    :data="getChartData(user)"
                                    :options="chartOptions"
                                    class="relative z-10 h-32 w-32"
                                />
                                <!-- Avatar di tengah doughnut -->
                                <Avatar
                                    class="absolute top-1/2 left-1/2 z-0 h-20 w-20 flex-shrink-0 -translate-x-1/2 -translate-y-1/2 transform shadow-lg ring-4 ring-background"
                                >
                                    <AvatarImage
                                        v-if="user.avatar"
                                        :src="`/storage/${user.avatar}`"
                                        :alt="user.name"
                                    />
                                    <AvatarFallback>
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
                                    class="absolute top-1/2 left-1/2 z-0 h-24 w-24 flex-shrink-0 -translate-x-1/2 -translate-y-1/2 transform shadow-lg ring-4 ring-background"
                                >
                                    <AvatarImage
                                        v-if="user.avatar"
                                        :src="`/storage/${user.avatar}`"
                                        :alt="user.name"
                                    />
                                    <AvatarFallback>
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

                        <div class="mt-auto w-full text-center">
                            <p
                                class="truncate text-base font-bold text-foreground"
                            >
                                {{ user.name }}
                            </p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ user.position }}
                            </p>
                            <p
                                class="truncate text-sm text-muted-foreground"
                            >
                                Total : {{ getTotalTasks(user) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <ScrollBar orientation="horizontal" />
        </ScrollArea>
    </div>
</template>
