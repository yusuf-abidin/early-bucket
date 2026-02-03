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
                        class="flex h-full max-w-xs min-w-52 cursor-pointer flex-col items-center rounded-xl border border-border bg-card p-4 text-card-foreground shadow-md"
                    >
                        <div class="relative flex h-32 w-full justify-center">
                            <template v-if="hasMemos(user)">
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
                            <p class="truncate text-sm text-muted-foreground">
                                Total : {{ getTotalMemos(user) }}
                            </p>
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
                        class="flex h-full max-w-xs min-w-52 cursor-pointer flex-col items-center rounded-xl border border-border bg-card p-4 text-card-foreground shadow-md"
                    >
                        <!-- Area atas (samakan dengan card user) -->
                        <div class="relative flex h-32 w-full justify-center">
                            <Avatar
                                class="absolute top-1/2 left-1/2 z-0 h-24 w-24 flex-shrink-0 -translate-x-1/2 -translate-y-1/2 transform shadow-lg ring-4 ring-background"
                            >
                                <AvatarFallback class="bg-primary/10">
                                    <FolderClosed
                                        class="h-10 w-10 text-primary"
                                    />
                                </AvatarFallback>
                            </Avatar>
                        </div>

                        <!-- Area bawah -->
                        <div class="mt-auto w-full text-center">
                            <p
                                class="truncate text-base font-bold text-foreground"
                            >
                                Total Arsip
                            </p>
                            <p class="truncate text-sm text-muted-foreground">
                                Total : {{ total_archive }}
                            </p>
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
