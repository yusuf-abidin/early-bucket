<script setup lang="ts">
import { Button } from '@/components/ui/button';
import RlqhLayout from '@/layouts/RlqhLayout.vue';
import rlqh from '@/routes/rlqh';
import { Article, BreadcrumbItem, LaravelPaginator } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import CardArticle from '@/components/CardArticle.vue';
import DialogDeleteArticle from '@/components/DialogDeleteArticle.vue';
import { computed, ref } from 'vue';
import TaskHistoryPagination from '@/components/TaskHistoryPagination.vue';

const props = defineProps<{
    articles: LaravelPaginator<Article>;
    status: string | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'News',
        href: '#',
    },
];

const selectedArticle = ref<Article | null>(null);

const createArticle = () => {
    router.visit(rlqh.news.create().url);
};
const activeStatus = computed(() => props.status ?? null);

const filterStatus = (status: string | null) => {
    if (status === null) {
        router.visit(rlqh.news.authorIndex().url);
    } else {
        router.visit(rlqh.news.authorIndex().url, {
            data: {
                status: status,
            },
        });
    }
};

const user = usePage().props.auth.user;

const edit = (article: Article) => {
    router.visit(rlqh.news.edit(article.id).url);
};

const handleDelete = (article: Article) => {
    selectedArticle.value = article;
};

const showArticle = (article: Article) => {
    router.visit(rlqh.news.show(article.id).url);
};
</script>

<template>
    <Head title="News" />

    <RlqhLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center gap-2">
                <!-- Draft -->
                <Button
                    @click="filterStatus('draft')"
                    size="sm"
                    :variant="
                        activeStatus === 'draft' ? 'secondary' : 'outline'
                    "
                >
                    Draft
                </Button>

                <!-- Published -->
                <Button
                    @click="filterStatus('published')"
                    size="sm"
                    :variant="
                        activeStatus === 'published' ? 'secondary' : 'outline'
                    "
                >
                    Diterbitkan
                </Button>

                <!-- Semua -->
                <Button
                    @click="filterStatus(null)"
                    size="sm"
                    :variant="activeStatus === null ? 'secondary' : 'ghost'"
                >
                    Semua News
                </Button>

                <!-- Spacer -->
                <div class="flex-1"></div>

                <!-- CTA -->
                <Button size="sm" @click="createArticle" variant="default">
                    + Buat News
                </Button>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <CardArticle
                    v-for="article in props.articles.data"
                    :key="article.id"
                    :article="article"
                    :is-admin="user.role === 'admin'"
                    @edit="edit(article)"
                    @delete="handleDelete(article)"
                    @open="showArticle(article)"
                />
            </div>

            <div
                v-if="props.articles.data.length === 0"
                class="flex h-full flex-col items-center justify-center text-center"
            >
                <h2 class="text-lg font-semibold text-gray-700">
                    Belum ada News
                </h2>
                <p class="text-sm text-gray-500">
                    Tidak ada news yang tersedia saat ini. Silakan buat
                    news.
                </p>
                <Button
                    size="sm"
                    @click="createArticle"
                    variant="default"
                    class="mt-4"
                >
                    + Buat News
                </Button>
            </div>
            <TaskHistoryPagination
                v-if="props.articles.data.length > 0"
                :current_page="props.articles.current_page"
                :last_page="props.articles.last_page"
                :links="props.articles.links"
            />
        </div>

        <DialogDeleteArticle v-model:selected-article="selectedArticle" />
    </RlqhLayout>
</template>

<style scoped></style>
