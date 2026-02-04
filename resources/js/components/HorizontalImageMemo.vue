<script setup lang="ts">
import { User } from '@/types';
import memos from '@/routes/memos';
import { router } from '@inertiajs/vue3';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { FolderClosed } from 'lucide-vue-next';
import { ArcElement, Chart as ChartJS, Legend, Title, Tooltip } from 'chart.js';
import { Pie } from 'vue-chartjs';

ChartJS.register(Title, Tooltip, Legend, ArcElement);
interface UserMemoSummary extends Pick<
    User,
    'id' | 'name' | 'avatar' | 'position'
> {
    pending_count: number;
    near_overdue_count: number;
    overdue_count: number;
}

const props = defineProps<{
    users_summary: UserMemoSummary[];
    total_archive: number;
}>();

const hasMemos = (user: UserMemoSummary) => {
    return (
        user.pending_count > 0 ||
        user.near_overdue_count > 0 ||
        user.overdue_count > 0
    );
};

const handleFilter = (userId: number) => {
    const currentUserId = new URLSearchParams(window.location.search).get(
        'user_id',
    );
    const query = currentUserId == userId.toString() ? {} : { user_id: userId };
    const url = memos.index().url;

    router.get(url, query, {
        preserveState: true,
        replace: true,
    });
};

const getChartData = (user: UserMemoSummary) => {
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
};

const getTotalMemos = (user: UserMemoSummary) => {
    return user.pending_count + user.near_overdue_count + user.overdue_count;
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '70%',
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            position: 'nearest',
            callbacks: {
                label: (context) => {
                    return `${context.label}: ${context.raw}`;
                },
            },
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
                                <template v-if="hasMemos(user)">
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
                                    {{ getTotalMemos(user) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD ARCHIVE -->
                <div
                    class="flex flex-col items-center transition-all hover:scale-105"
                    @click="
                        router.get(memos.archive().url, {}, { replace: true })
                    "
                >
                    <div
                        class="flex h-full max-w-xs min-w-52 cursor-pointer flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg transition-all hover:shadow-xl dark:border-gray-700 dark:bg-gray-800"
                    >
                        <!-- Header dengan warna corporate solid - Archive variant -->
                        <div
                            class="bg-gradient-to-r from-gray-600 to-gray-700 p-4 pb-12"
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

                        <!-- Avatar Container -->
                        <div class="relative -mt-14 flex justify-center px-4">
                            <div
                                class="relative flex h-32 w-full justify-center"
                            >
                                <Avatar
                                    class="absolute top-1/2 left-1/2 z-0 h-24 w-24 -translate-x-1/2 -translate-y-1/2 shadow-xl ring-4 ring-white dark:ring-gray-800"
                                >
                                    <AvatarFallback
                                        class="bg-gray-100 dark:bg-gray-700"
                                    >
                                        <FolderClosed
                                            class="h-10 w-10 text-gray-600 dark:text-gray-400"
                                        />
                                    </AvatarFallback>
                                </Avatar>
                            </div>
                        </div>

                        <!-- Info Section -->
                        <div class="mt-auto w-full p-4 pt-2 text-center">
                            <p
                                class="truncate text-base font-bold text-gray-900 dark:text-gray-100"
                            >
                                Total Arsip
                            </p>
                            <div
                                class="mt-2 flex items-center justify-center gap-2 rounded-lg bg-gray-50 px-2 py-1 dark:bg-gray-700/20"
                            >
                                <span
                                    class="text-xs text-gray-600 dark:text-gray-400"
                                    >Total:</span
                                >
                                <span
                                    class="text-sm font-bold text-gray-700 dark:text-gray-300"
                                >
                                    {{ total_archive }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END CARD ARCHIVE -->
            </div>
            <ScrollBar orientation="horizontal" />
        </ScrollArea>
    </div>
</template>

<style scoped></style>
