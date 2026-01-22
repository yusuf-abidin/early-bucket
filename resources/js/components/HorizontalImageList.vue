<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { UserSummary } from '@/types';
import { router } from '@inertiajs/vue3';
import tasks from '@/routes/tasks';

const props = defineProps<{
    users_summary: UserSummary[];
    mode?: 'pending' | 'history'; // ← tambahan
}>();

const mode = props.mode ?? 'pending'; // default ke pending

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
                        class="flex h-full max-w-xs min-w-52 cursor-pointer flex-col items-center rounded-xl border border-border bg-card p-4 text-card-foreground shadow-md"
                    >
                        <div class="mb-3 flex w-full items-start gap-3">
                            <Avatar
                                class="h-20 w-20 flex-shrink-0 shadow-lg ring-4 ring-background"
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

                            <div
                                class="mt-0.5 flex min-w-0 flex-1 flex-col gap-1"
                            >
                                <!-- === Mode: Pending / Default === -->
                                <template v-if="mode === 'pending'">
                                    <template v-if="hasTasks(user)">
                                        <span
                                            v-if="user.pending_count > 0"
                                            class="inline-flex items-center gap-x-1 rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-800 dark:bg-neutral-500/20 dark:text-neutral-400"
                                        >
                                            <svg
                                                class="size-3 shrink-0"
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <line
                                                    x1="12"
                                                    x2="12"
                                                    y1="2"
                                                    y2="6"
                                                ></line>
                                                <line
                                                    x1="12"
                                                    x2="12"
                                                    y1="18"
                                                    y2="22"
                                                ></line>
                                                <line
                                                    x1="4.93"
                                                    x2="7.76"
                                                    y1="4.93"
                                                    y2="7.76"
                                                ></line>
                                                <line
                                                    x1="16.24"
                                                    x2="19.07"
                                                    y1="16.24"
                                                    y2="19.07"
                                                ></line>
                                                <line
                                                    x1="2"
                                                    x2="6"
                                                    y1="12"
                                                    y2="12"
                                                ></line>
                                                <line
                                                    x1="18"
                                                    x2="22"
                                                    y1="12"
                                                    y2="12"
                                                ></line>
                                                <line
                                                    x1="4.93"
                                                    x2="7.76"
                                                    y1="19.07"
                                                    y2="16.24"
                                                ></line>
                                                <line
                                                    x1="16.24"
                                                    x2="19.07"
                                                    y1="7.76"
                                                    y2="4.93"
                                                ></line>
                                            </svg>
                                            {{ user.pending_count }} Pending
                                        </span>

                                        <span
                                            v-if="user.near_overdue_count > 0"
                                            class="inline-flex items-center gap-x-1 rounded-full bg-yellow-100 px-1.5 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-500"
                                        >
                                            <svg
                                                class="size-3.5 shrink-0"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="9"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                                <path
                                                    d="M12 6V12H6"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                            {{ user.near_overdue_count }} Mendekati Deadline
                                        </span>

                                        <span
                                            v-if="user.overdue_count > 0"
                                            class="inline-flex items-center gap-x-1 rounded-full bg-red-100 px-1.5 py-1 text-xs font-medium text-red-800 dark:bg-red-500/10 dark:text-red-500"
                                        >
                                            <svg
                                                class="size-3 shrink-0"
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path
                                                    d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"
                                                ></path>
                                                <path d="M12 9v4"></path>
                                                <path d="M12 17h.01"></path>
                                            </svg>
                                            {{ user.overdue_count }} Melewati
                                            deadline
                                        </span>
                                    </template>

                                    <Badge
                                        v-else
                                        variant="outline"
                                        class="w-fit px-2.5 py-1 text-xs font-medium whitespace-nowrap text-muted-foreground"
                                    >
                                        No Task
                                    </Badge>
                                </template>

                                <!-- === Mode: History === -->
                                <template v-else-if="mode === 'history'">
                                    <span
                                        v-if="hasHistory(user)"
                                        class="inline-flex items-center gap-x-1 rounded-full bg-teal-100 px-2 py-1 text-xs font-medium text-teal-800 dark:bg-teal-500/10 dark:text-teal-500"
                                    >
                                        <svg
                                            class="size-3 shrink-0"
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path
                                                d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"
                                            ></path>
                                            <path d="m9 12 2 2 4-4"></path>
                                        </svg>
                                        {{ user.completed_this_week_count }}
                                        Tugas Selesai
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex items-center gap-x-1 rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-800 dark:bg-neutral-500/20 dark:text-neutral-400"
                                    >
                                        <svg
                                            class="size-3.5 shrink-0"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <rect
                                                x="3"
                                                y="4"
                                                width="18"
                                                height="18"
                                                rx="2"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <path
                                                d="M16 2V6"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <path
                                                d="M8 2V6"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <path
                                                d="M3 10H21"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                            <line
                                                x1="8"
                                                y1="14"
                                                x2="16"
                                                y2="14"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                            />
                                        </svg>
                                        Tidak ada history
                                    </span>
                                </template>
                            </div>
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
                        </div>
                    </div>
                </div>
            </div>

            <ScrollBar orientation="horizontal" />
        </ScrollArea>
    </div>
</template>
