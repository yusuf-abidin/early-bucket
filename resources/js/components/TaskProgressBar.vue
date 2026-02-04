<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    taskStats: {
        type: Object,
        required: true,
        default: () => ({
            total: 0,
            pending: 0,
            near_deadline: 0,
            overdue: 0,
            completed: 0,
        }),
    },
});

const stats = computed(() => props.taskStats);

const overduePercentage = computed(() => {
    if (stats.value.total === 0) return 0;
    return (stats.value.overdue / stats.value.total) * 100;
});

const nearDeadlinePercentage = computed(() => {
    if (stats.value.total === 0) return 0;
    return (stats.value.near_deadline / stats.value.total) * 100;
});

const pendingPercentage = computed(() => {
    if (stats.value.total === 0) return 0;
    return (stats.value.pending / stats.value.total) * 100;
});

const completedPercentage = computed(() => {
    if (stats.value.total === 0) return 0;
    return (stats.value.completed / stats.value.total) * 100;
});
</script>

<template>
    <div class="rounded-lg p-4 shadow-md border">
        <!-- Header -->
        <div class="mb-1">
            <h3 class="text-lg font-semibold">
                Progress 30 Hari Terakhir
            </h3>
            <p class="text-sm">
                Total Agenda: {{ stats.total }}
            </p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-2">
            <div
                class="flex h-5 w-full overflow-hidden rounded-full bg-gray-200"
            >
                <template v-if="stats.total > 0">
                    <!-- Overdue (Red) -->
                    <div
                        v-if="overduePercentage > 0"
                        :style="{ width: overduePercentage + '%' }"
                        class="flex items-center justify-center bg-red-500 transition-all duration-300"
                        :title="`Melewati Deadline: ${stats.overdue}`"
                    >
                        <span
                            v-if="overduePercentage > 8"
                            class="text-xs font-medium text-white"
                        >
                            {{ stats.overdue }}
                        </span>
                    </div>

                    <!-- Near Deadline (Yellow) -->
                    <div
                        v-if="nearDeadlinePercentage > 0"
                        :style="{ width: nearDeadlinePercentage + '%' }"
                        class="flex items-center justify-center bg-yellow-400 transition-all duration-300"
                        :title="`Near Deadline: ${stats.near_deadline}`"
                    >
                        <span
                            v-if="nearDeadlinePercentage > 8"
                            class="text-xs font-medium text-white"
                        >
                            {{ stats.near_deadline }}
                        </span>
                    </div>

                    <!-- Pending (Gray) -->
                    <div
                        v-if="pendingPercentage > 0"
                        :style="{ width: pendingPercentage + '%' }"
                        class="flex items-center justify-center bg-gray-400 transition-all duration-300"
                        :title="`Pending: ${stats.pending}`"
                    >
                        <span
                            v-if="pendingPercentage > 8"
                            class="text-xs font-medium text-white"
                        >
                            {{ stats.pending }}
                        </span>
                    </div>

                    <!-- Completed (Blue) -->
                    <div
                        v-if="completedPercentage > 0"
                        :style="{ width: completedPercentage + '%' }"
                        class="flex items-center justify-center bg-blue-500 transition-all duration-300"
                        :title="`Completed: ${stats.completed}`"
                    >
                        <span
                            v-if="completedPercentage > 8"
                            class="text-xs font-medium text-white"
                        >
                            {{ stats.completed }}
                        </span>
                    </div>
                </template>

                <!-- No Tasks State -->
                <template v-else>
                    <div class="flex w-full items-center justify-center">
                        <span class="text-xs font-medium"
                            >Tidak ada data debitur menabung</span
                        >
                    </div>
                </template>
            </div>
        </div>

        <!-- Legend -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <!-- Overdue -->
            <div class="flex items-center space-x-2">
                <div class="h-4 w-4 rounded bg-red-500"></div>
                <div class="flex-1">
                    <p class="text-xs font-medium">Melewati Deadline</p>
                    <p class="text-sm font-semibold">
                        {{ stats.overdue }}
                    </p>
                </div>
            </div>

            <!-- Near Deadline -->
            <div class="flex items-center space-x-2">
                <div class="h-4 w-4 rounded bg-yellow-400"></div>
                <div class="flex-1">
                    <p class="text-xs font-medium">
                        Mendekati Deadline
                    </p>
                    <p class="text-sm font-semibold">
                        {{ stats.near_deadline }}
                    </p>
                </div>
            </div>

            <!-- Pending -->
            <div class="flex items-center space-x-2">
                <div class="h-4 w-4 rounded bg-gray-400"></div>
                <div class="flex-1">
                    <p class="text-xs font-medium">Pending</p>
                    <p class="text-sm font-semibold">
                        {{ stats.pending }}
                    </p>
                </div>
            </div>

            <!-- Completed -->
            <div class="flex items-center space-x-2">
                <div class="h-4 w-4 rounded bg-blue-500"></div>
                <div class="flex-1">
                    <p class="text-xs font-medium">Selesai</p>
                    <p class="text-sm font-semibold">
                        {{ stats.completed }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
