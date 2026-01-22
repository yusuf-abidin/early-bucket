<script setup lang="ts">
import { User } from '@/types';
import memos from '@/routes/memos';
import { router } from '@inertiajs/vue3';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { FolderClosed } from 'lucide-vue-next';

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
                                <template v-if="hasMemos(user)">
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
                                        {{ user.near_overdue_count }} Mendekati
                                        Deadline
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
                                    No Memo
                                </Badge>
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
                <!-- CARD ARCHIVE -->
                <div
                    class="flex flex-col items-center transition-all hover:scale-105"
                    @click="router.get(memos.archive().url, {}, { replace: true })"
                >
                    <div
                        class="flex h-full max-w-xs min-w-52 cursor-pointer flex-col items-center rounded-xl border border-border bg-card p-4 text-card-foreground shadow-md"
                    >
                        <div
                            class="mb-3 flex w-full items-center justify-center gap-3"
                        >
                            <Avatar
                                class="h-20 w-20 flex-shrink-0 shadow-lg ring-4 ring-background"
                            >
                                <AvatarFallback class="bg-primary/10">
                                    <FolderClosed
                                        class="h-10 w-10 text-primary"
                                    />
                                </AvatarFallback>
                            </Avatar>
                        </div>

                        <div class="mt-auto w-full text-center">
                            <p
                                class="truncate text-base font-medium text-foreground"
                            >
                                Total Arsip
                            </p>
                            <p
                                class="truncate text-base font-medium text-primary"
                            >
                                {{ total_archive }}
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
