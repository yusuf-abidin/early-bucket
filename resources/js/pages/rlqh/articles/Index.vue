<script setup lang="ts">
import RlqhLayout from '@/layouts/RlqhLayout.vue';
import { Article, BreadcrumbItem, LaravelPaginator } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import CardArticle from '@/components/CardArticle.vue';
import rlqh from '@/routes/rlqh';
import TaskHistoryPagination from '@/components/TaskHistoryPagination.vue';

const props = defineProps<{
    articles: LaravelPaginator<Article>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'News',
        href: '#',
    },
];

const user = usePage().props.auth.user;

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
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <CardArticle
                    v-for="article in props.articles.data"
                    :key="article.id"
                    :article="article"
                    :is-admin="user.role === 'admin'"
                    @open="showArticle(article)"
                />
            </div>

            <TaskHistoryPagination
                :current_page="props.articles.current_page"
                :last_page="props.articles.last_page"
                :links="props.articles.links"
            />
        </div>
    </RlqhLayout>
</template>

<style scoped></style>
