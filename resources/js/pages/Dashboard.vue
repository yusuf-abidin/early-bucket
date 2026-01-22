<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, Memo, Task } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    CheckCircle2,
    Clock,
    AlertCircle,
    FileText,
    TrendingUp,
    Calendar,
    Mail,
    BarChart3,
} from 'lucide-vue-next';

interface Stats {
    totalTasks: number;
    completedTasks: number;
    pendingTasks: number;
    overdueTasks: number;
    totalMemos: number;
    completionRate: number;
}

interface CategoryStat {
    name: string;
    total: number;
    completed: number;
    percentage: number;
    color?: {
        class: string;
    };
}

interface WeeklyActivity {
    date: string;
    tasks_completed: number;
    memos_completed: number;
}

const props = defineProps<{
    stats: Stats;
    upcomingTasks: Task[];
    recentMemos: Memo[];
    tasksByCategory: CategoryStat[];
    weeklyActivity: WeeklyActivity[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const isOverdue = (dueDate: string) => {
    return new Date(dueDate) < new Date();
};

const maxActivityValue = computed(() => {
    const maxTasks = Math.max(
        ...props.weeklyActivity.map((a) => a.tasks_completed),
    );
    const maxMemos = Math.max(
        ...props.weeklyActivity.map((a) => a.memos_completed),
    );
    return Math.max(maxTasks, maxMemos, 1);
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <!-- Total Tasks -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-gradient-to-br from-blue-50 to-blue-100/50 p-6 transition-all hover:shadow-lg dark:border-sidebar-border dark:from-blue-950/30 dark:to-blue-900/20"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p
                                class="text-sm font-medium text-blue-700 dark:text-blue-300"
                            >
                                Total Tasks
                                <span class="text-xs">(Last 30 days)</span>
                            </p>
                            <h3
                                class="mt-2 text-3xl font-bold text-blue-900 dark:text-blue-100"
                            >
                                {{ stats.totalTasks }}
                            </h3>
                            <div class="mt-3 flex items-center gap-2">
                                <div
                                    class="flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400"
                                >
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    <span
                                        >{{
                                            stats.completedTasks
                                        }}
                                        Completed</span
                                    >
                                </div>
                            </div>
                        </div>
                        <div
                            class="rounded-lg bg-blue-600/10 p-3 dark:bg-blue-400/10"
                        >
                            <BarChart3
                                class="h-6 w-6 text-blue-600 dark:text-blue-400"
                            />
                        </div>
                    </div>
                    <div
                        class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-blue-600/5 dark:bg-blue-400/5"
                    ></div>
                </div>

                <!-- Pending Tasks -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-gradient-to-br from-amber-50 to-amber-100/50 p-6 transition-all hover:shadow-lg dark:border-sidebar-border dark:from-amber-950/30 dark:to-amber-900/20"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p
                                class="text-sm font-medium text-amber-700 dark:text-amber-300"
                            >
                                Pending Tasks
                                <span class="text-xs">(Last 30 days)</span>
                            </p>
                            <h3
                                class="mt-2 text-3xl font-bold text-amber-900 dark:text-amber-100"
                            >
                                {{ stats.pendingTasks }}
                            </h3>
                            <div class="mt-3 flex items-center gap-2">
                                <div
                                    class="flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400"
                                >
                                    <AlertCircle class="h-3.5 w-3.5" />
                                    <span
                                        >{{ stats.overdueTasks }} Overdue</span
                                    >
                                </div>
                            </div>
                        </div>
                        <div
                            class="rounded-lg bg-amber-600/10 p-3 dark:bg-amber-400/10"
                        >
                            <Clock
                                class="h-6 w-6 text-amber-600 dark:text-amber-400"
                            />
                        </div>
                    </div>
                    <div
                        class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-amber-600/5 dark:bg-amber-400/5"
                    ></div>
                </div>

                <!-- Completion Rate -->
                <div
                    class="group relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-6 transition-all hover:shadow-lg dark:border-sidebar-border dark:from-emerald-950/30 dark:to-emerald-900/20"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p
                                class="text-sm font-medium text-emerald-700 dark:text-emerald-300"
                            >
                                Completion Rate
                                <span class="text-xs">(Last 30 days)</span>
                            </p>
                            <h3
                                class="mt-2 text-3xl font-bold text-emerald-900 dark:text-emerald-100"
                            >
                                {{ stats.completionRate }}%
                            </h3>
                            <div class="mt-3">
                                <div
                                    class="h-2 w-full overflow-hidden rounded-full bg-emerald-200 dark:bg-emerald-900/30"
                                >
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600 transition-all duration-500"
                                        :style="{
                                            width: `${stats.completionRate}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="rounded-lg bg-emerald-600/10 p-3 dark:bg-emerald-400/10"
                        >
                            <TrendingUp
                                class="h-6 w-6 text-emerald-600 dark:text-emerald-400"
                            />
                        </div>
                    </div>
                    <div
                        class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-emerald-600/5 dark:bg-emerald-400/5"
                    ></div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Upcoming Tasks -->
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-white/50 backdrop-blur-sm lg:col-span-2 dark:border-sidebar-border dark:bg-sidebar/30"
                >
                    <div
                        class="border-b border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30"
                            >
                                <Calendar
                                    class="h-5 w-5 text-blue-600 dark:text-blue-400"
                                />
                            </div>
                            <div>
                                <h2
                                    class="text-lg font-semibold text-sidebar-foreground"
                                >
                                    Upcoming Tasks
                                </h2>
                                <p class="text-sm text-muted-foreground">
                                    Tasks due in the next 7 days
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div
                            v-if="upcomingTasks.length === 0"
                            class="py-12 text-center"
                        >
                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30"
                            >
                                <CheckCircle2
                                    class="h-6 w-6 text-green-600 dark:text-green-400"
                                />
                            </div>
                            <p class="text-sm text-muted-foreground">
                                No upcoming tasks
                            </p>
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="task in upcomingTasks"
                                :key="task.id"
                                class="group flex items-start gap-4 rounded-lg border border-sidebar-border/50 bg-white p-4 transition-all hover:border-sidebar-border hover:shadow-md dark:border-sidebar-border/30 dark:bg-sidebar/50 dark:hover:border-sidebar-border/70"
                            >
                                <div
                                    class="mt-0.5 flex-shrink-0 rounded-md p-2"
                                    :class="
                                        task.category!.color?.class ||
                                        'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                                    "
                                >
                                    <FileText class="h-4 w-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="font-medium text-sidebar-foreground group-hover:text-blue-600 dark:group-hover:text-blue-400"
                                    >
                                        {{ task.task_description }}
                                    </p>
                                    <div
                                        class="mt-2 flex flex-wrap items-center gap-3 text-xs text-muted-foreground"
                                    >
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1"
                                            :class="
                                                task.category!.color?.class ||
                                                'bg-gray-100 text-gray-600 dark:bg-gray-800'
                                            "
                                        >
                                            {{ task.category!.name }}
                                        </span>
                                        <span
                                            class="flex items-center gap-1"
                                            :class="{
                                                'text-red-600 dark:text-red-400':
                                                    isOverdue(task.due_date!),
                                            }"
                                        >
                                            <Clock class="h-3.5 w-3.5" />
                                            {{ formatDate(task.due_date!) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Categories Progress -->
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-white/50 backdrop-blur-sm dark:border-sidebar-border dark:bg-sidebar/30"
                >
                    <div
                        class="border-b border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30"
                            >
                                <BarChart3
                                    class="h-5 w-5 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                            <div>
                                <h2
                                    class="text-lg font-semibold text-sidebar-foreground"
                                >
                                    Progress by Category
                                </h2>
                                <p class="text-sm text-muted-foreground">
                                    Task completion rates
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div
                                v-for="category in tasksByCategory"
                                :key="category.name"
                            >
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-block h-3 w-3 rounded-full"
                                            :class="
                                                category.color?.class ||
                                                'bg-gray-400'
                                            "
                                        ></span>
                                        <span
                                            class="text-sm font-medium text-sidebar-foreground"
                                            >{{ category.name }}</span
                                        >
                                    </div>
                                    <span
                                        class="text-sm font-semibold text-sidebar-foreground"
                                        >{{ category.percentage }}%</span
                                    >
                                </div>
                                <div
                                    class="h-2.5 w-full overflow-hidden rounded-full bg-sidebar-border/30"
                                >
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :class="
                                            category.color?.class ||
                                            'bg-gray-400'
                                        "
                                        :style="{
                                            width: `${category.percentage}%`,
                                        }"
                                    ></div>
                                </div>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ category.completed }} of
                                    {{ category.total }} completed
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Recent Memos -->
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-white/50 backdrop-blur-sm dark:border-sidebar-border dark:bg-sidebar/30"
                >
                    <div
                        class="border-b border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-indigo-100 p-2 dark:bg-indigo-900/30"
                            >
                                <Mail
                                    class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
                                />
                            </div>
                            <div>
                                <h2
                                    class="text-lg font-semibold text-sidebar-foreground"
                                >
                                    Recent Memos
                                </h2>
                                <p class="text-sm text-muted-foreground">
                                    Latest {{ stats.totalMemos }} memos received
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div
                            v-if="recentMemos.length === 0"
                            class="py-12 text-center"
                        >
                            <p class="text-sm text-muted-foreground">
                                No memos yet
                            </p>
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="memo in recentMemos"
                                :key="memo.id"
                                class="group rounded-lg border border-sidebar-border/50 bg-white p-4 transition-all hover:border-sidebar-border hover:shadow-md dark:border-sidebar-border/30 dark:bg-sidebar/50 dark:hover:border-sidebar-border/70"
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        class="mt-0.5 flex-shrink-0 rounded-md p-2"
                                        :class="
                                            memo.category.color?.class ||
                                            'bg-gray-100 text-gray-600 dark:bg-gray-800'
                                        "
                                    >
                                        <Mail class="h-4 w-4" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="font-medium text-sidebar-foreground group-hover:text-indigo-600 dark:group-hover:text-indigo-400"
                                        >
                                            {{ memo.subject }}
                                        </p>
                                        <p
                                            class="mt-1 text-sm text-muted-foreground"
                                        >
                                            From: {{ memo.origin }}
                                        </p>
                                        <div
                                            class="mt-2 flex items-center gap-2 text-xs text-muted-foreground"
                                        >
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1"
                                                :class="
                                                    memo.category.color
                                                        ?.class ||
                                                    'bg-gray-100 text-gray-600 dark:bg-gray-800'
                                                "
                                            >
                                                {{ memo.category.name }}
                                            </span>
                                            <span>{{
                                                formatDate(memo.received_at!)
                                            }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weekly Activity Chart -->
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-white/50 backdrop-blur-sm dark:border-sidebar-border dark:bg-sidebar/30"
                >
                    <div
                        class="border-b border-sidebar-border/70 p-6 dark:border-sidebar-border"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-lg bg-teal-100 p-2 dark:bg-teal-900/30"
                            >
                                <TrendingUp
                                    class="h-5 w-5 text-teal-600 dark:text-teal-400"
                                />
                            </div>
                            <div>
                                <h2
                                    class="text-lg font-semibold text-sidebar-foreground"
                                >
                                    Weekly Activity
                                </h2>
                                <p class="text-sm text-muted-foreground">
                                    Last 7 days performance
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center gap-4 text-xs">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="h-3 w-3 rounded-sm bg-blue-500"
                                    ></div>
                                    <span class="text-muted-foreground"
                                        >Tasks</span
                                    >
                                </div>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="h-3 w-3 rounded-sm bg-purple-500"
                                    ></div>
                                    <span class="text-muted-foreground"
                                        >Memos</span
                                    >
                                </div>
                            </div>
                            <div
                                class="flex h-48 items-end justify-between gap-2"
                            >
                                <div
                                    v-for="(activity, index) in weeklyActivity"
                                    :key="index"
                                    class="flex flex-1 flex-col items-center gap-2"
                                >
                                    <!-- BAR GROUP -->
                                    <div class="flex w-full items-end gap-1">
                                        <!-- TASK BAR -->
                                        <div
                                            class="group relative flex-1 rounded-t bg-blue-500 transition-all hover:bg-blue-600"
                                            :style="{
                                                height: `${(activity.tasks_completed / maxActivityValue) * 160}px`,
                                                minHeight:
                                                    activity.tasks_completed > 0
                                                        ? '8px'
                                                        : '0',
                                            }"
                                        >
                                            <!-- Tooltip -->
                                            <div
                                                v-if="
                                                    activity.tasks_completed > 0
                                                "
                                                class="pointer-events-none absolute -top-7 left-1/2 -translate-x-1/2 rounded bg-blue-600 px-2 py-0.5 text-xs text-white opacity-0 transition group-hover:opacity-100"
                                            >
                                                {{ activity.tasks_completed }}
                                            </div>
                                        </div>

                                        <!-- MEMO BAR -->
                                        <div
                                            class="group relative flex-1 rounded-t bg-purple-500 transition-all hover:bg-purple-600"
                                            :style="{
                                                height: `${(activity.memos_completed / maxActivityValue) * 160}px`,
                                                minHeight:
                                                    activity.memos_completed > 0
                                                        ? '8px'
                                                        : '0',
                                            }"
                                        >
                                            <!-- Tooltip -->
                                            <div
                                                v-if="
                                                    activity.memos_completed > 0
                                                "
                                                class="pointer-events-none absolute -top-7 left-1/2 -translate-x-1/2 rounded bg-purple-600 px-2 py-0.5 text-xs text-white opacity-0 transition group-hover:opacity-100"
                                            >
                                                {{ activity.memos_completed }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- DATE LABEL -->
                                    <span class="text-xs text-muted-foreground">
                                        {{ activity.date }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
