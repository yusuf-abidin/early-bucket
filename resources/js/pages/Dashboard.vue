<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
    type ChartOptions,
} from 'chart.js';
import { dashboard } from '@/routes';
import { MapPin, Building, Pencil } from 'lucide-vue-next';
import admin from '@/routes/admin';
import memos from '@/routes/memos';
import tasks from '@/routes/tasks';
import debtorSavings from '@/routes/debtor-savings';

ChartJS.register(ArcElement, Tooltip, Legend);

interface Props {
    overview: {
        totalAreas: number;
        totalBranches: number;
    };
    pendingMemo: {
        total: number;
        pending: number;
        approaching_deadline: number;
        overdue: number;
    };
    pendingMatter: {
        total: number;
        pending: number;
        approaching_deadline: number;
        overdue: number;
    };
    debiturMenabung: {
        total: number;
        pending: number;
        approaching_deadline: number;
        overdue: number;
        completed: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// Chart configurations
const createChartOptions = (hasData: boolean): ChartOptions<'doughnut'> => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: hasData,
            position: 'bottom',
            labels: {
                padding: 16,
                usePointStyle: true,
                pointStyle: 'circle',
            },
        },
        tooltip: {
            enabled: hasData,
            padding: 12,
            cornerRadius: 8,
            callbacks: {
                label: function (context) {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const total = context.dataset.data.reduce(
                        (a: number, b: number) => a + b,
                        0,
                    );
                    const percentage =
                        total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    return `${label}: ${value} (${percentage}%)`;
                },
            },
        },
    },
    cutout: '65%',
    events: hasData
        ? ['mousemove', 'mouseout', 'click', 'touchstart', 'touchmove']
        : [],
});

const pendingMemoChartOptions = computed(() =>
    createChartOptions(hasPendingMemoData.value),
);
const pendingMatterChartOptions = computed(() =>
    createChartOptions(hasPendingMatterData.value),
);
const debiturMenabungChartOptions = computed(() =>
    createChartOptions(hasDebiturMenabungData.value),
);

const hasPendingMemoData = computed(
    () =>
        props.pendingMemo.pending > 0 ||
        props.pendingMemo.approaching_deadline > 0 ||
        props.pendingMemo.overdue > 0,
);

const hasPendingMatterData = computed(
    () =>
        props.pendingMatter.pending > 0 ||
        props.pendingMatter.approaching_deadline > 0 ||
        props.pendingMatter.overdue > 0,
);

const hasDebiturMenabungData = computed(
    () =>
        props.debiturMenabung.pending > 0 ||
        props.debiturMenabung.approaching_deadline > 0 ||
        props.debiturMenabung.overdue > 0 ||
        props.debiturMenabung.completed > 0,
);

// Pending Memo Chart Data
const pendingMemoChartData = computed(() => ({
    labels: hasPendingMemoData.value
        ? ['Pending', 'Mendekati Deadline', 'Melewati Deadline']
        : ['Tidak Ada Data'],
    datasets: [
        {
            data: hasPendingMemoData.value
                ? [
                      props.pendingMemo.pending,
                      props.pendingMemo.approaching_deadline,
                      props.pendingMemo.overdue,
                  ]
                : [1],
            borderColor: hasPendingMemoData.value ? 'transparent' : '#E2E8F0',
            backgroundColor: hasPendingMemoData.value
                ? ['#9CA3AF', '#FBBF24', '#EF4444']
                : ['transparent'],
            borderWidth: hasPendingMemoData.value ? 0 : 3,
            hoverOffset: hasPendingMemoData.value ? 8 : 0,
        },
    ],
}));

// Pending Matter Chart Data
const pendingMatterChartData = computed(() => ({
    labels: hasPendingMatterData.value
        ? ['Pending', 'Mendekati Deadline', 'Melewati Deadline']
        : ['Tidak Ada Data'],
    datasets: [
        {
            data: hasPendingMatterData.value
                ? [
                      props.pendingMatter.pending,
                      props.pendingMatter.approaching_deadline,
                      props.pendingMatter.overdue,
                  ]
                : [1],
            borderColor: hasPendingMatterData.value ? 'transparent' : '#E2E8F0',
            backgroundColor: hasPendingMatterData.value
                ? ['#9CA3AF', '#FBBF24', '#EF4444']
                : ['transparent'],
            borderWidth: hasPendingMatterData.value ? 0 : 3,
            hoverOffset: hasPendingMatterData.value ? 8 : 0,
        },
    ],
}));

// Debitur Menabung Chart Data
const debiturMenabungChartData = computed(() => ({
    labels: hasDebiturMenabungData.value
        ? ['Pending', 'Mendekati Deadline', 'Melewati Deadline', 'Selesai']
        : ['Tidak Ada Data'],
    datasets: [
        {
            data: hasDebiturMenabungData.value
                ? [
                      props.debiturMenabung.pending,
                      props.debiturMenabung.approaching_deadline,
                      props.debiturMenabung.overdue,
                      props.debiturMenabung.completed,
                  ]
                : [1],
            borderColor: hasDebiturMenabungData.value
                ? 'transparent'
                : '#E2E8F0',
            backgroundColor: hasDebiturMenabungData.value
                ? ['#9CA3AF', '#FBBF24', '#EF4444', '#2B7FFF']
                : ['transparent'],
            borderWidth: hasDebiturMenabungData.value ? 0 : 3,
            hoverOffset: hasDebiturMenabungData.value ? 8 : 0,
        },
    ],
}));

const animate = ref(false);

onMounted(() => {
    setTimeout(() => {
        animate.value = true;
    }, 100);
});

const photoSrc = ref(
    '/storage/early-bucket-hero.png?t=' + new Date().getTime(),
);

const fileInput = ref<HTMLInputElement | null>(null);
const openFileInput = () => {
    fileInput.value?.click();
};

const uploadPhoto = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('image', file);

    router.post(admin.dashboard.changeHero().url, formData, {
        forceFormData: true,
        onSuccess: () => {
            photoSrc.value =
                '/storage/early-bucket-hero.png?t=' + new Date().getTime();
            target.value = '';
        },
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col">
            <!-- Hero Section -->
            <div
                class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900"
            >
                <!-- Background overlay -->
                <div
                    class="absolute inset-0 bg-gradient-to-r from-slate-900/90 via-blue-900/80 to-slate-900/90"
                ></div>

                <div class="relative px-6 py-4 md:py-4 lg:py-4">
                    <div class="mx-auto max-w-7xl">
                        <!-- Responsive layout: mobile = stack vertikal, desktop = side-by-side -->
                        <div
                            class="grid grid-cols-1 items-center gap-6 md:grid-cols-2 lg:gap-16"
                        >
                            <!-- Kolom kiri: Teks -->
                            <div
                                class="order-1 transition-all duration-700 ease-out"
                                :class="
                                    animate
                                        ? 'translate-y-0 opacity-100'
                                        : 'translate-y-8 opacity-0'
                                "
                            >
                                <h1
                                    class="mb-3 text-3xl font-bold tracking-tight text-white md:text-4xl lg:text-5xl"
                                >
                                    Early Bucket
                                </h1>
                                <p
                                    class="text-base font-medium text-blue-200 md:text-lg lg:text-xl"
                                >
                                    Consumer Collection, Recovery, & Asset Sales
                                    Division 1
                                </p>
                            </div>

                            <!-- Kolom kanan: Foto dengan edit -->
                            <div
                                class="order-2 transition-all delay-150 duration-700 ease-out md:order-2"
                                :class="
                                    animate
                                        ? 'translate-y-0 opacity-100'
                                        : 'translate-y-8 opacity-0'
                                "
                            >
                                <div
                                    class="group relative mx-auto overflow-hidden rounded-2xl shadow-2xl ring-4 ring-white/20"
                                >
                                    <!-- Foto -->
                                    <img
                                        :src="photoSrc"
                                        alt="Tim Early Bucket"
                                        class="mx-auto max-h-[250px] w-full object-cover object-center transition-transform duration-500"
                                    />

                                    <!-- Overlay gelap -->
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"
                                    ></div>

                                    <!-- Caption -->
                                    <div
                                        class="absolute right-0 bottom-0 left-0 p-6 text-center"
                                    >
                                        <p
                                            class="text-sm font-medium text-white/90 md:text-base"
                                        >
                                            Tim Early Bucket
                                        </p>
                                    </div>

                                    <!-- Tombol Edit (Pencil)  -->
                                    <div
                                        v-if="
                                            $page.props.auth?.user?.role ===
                                            'admin'
                                        "
                                        class="absolute top-4 right-4 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                    >
                                        <button
                                            @click.prevent="openFileInput"
                                            class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition hover:bg-white/30"
                                            title="Ganti Foto"
                                        >
                                            <!-- Pencil Icon (Heroicons) -->
                                            <Pencil class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Hidden File Input -->
                                <input
                                    v-if="
                                        $page.props.auth?.user?.role === 'admin'
                                    "
                                    type="file"
                                    ref="fileInput"
                                    accept="image/*"
                                    @change="uploadPhoto"
                                    class="hidden"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 px-6 py-6 md:p-8">
                <div class="mx-auto max-w-7xl space-y-6 md:space-y-8">
                    <!-- Overview Cards -->
                    <div
                        class="grid grid-cols-1 gap-5 transition-all delay-100 duration-700 ease-out md:grid-cols-2"
                        :class="
                            animate
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <!-- Total Area Card -->
                        <div
                            class="group overflow-hidden rounded-xl border border-slate-200 shadow-sm transition-all duration-300 hover:shadow-lg"
                        >
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p
                                            class="mb-1 text-xs font-semibold tracking-wider uppercase md:text-sm"
                                        >
                                            Total Area
                                        </p>
                                        <p
                                            class="text-4xl font-bold tracking-tight text-blue-600 md:text-4xl"
                                        >
                                            {{ overview.totalAreas }}
                                        </p>
                                    </div>
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-xl bg-blue-50 transition-transform duration-300 group-hover:scale-105 dark:bg-blue-900/20"
                                    >
                                        <MapPin class="h-7 w-7 text-blue-600" />
                                    </div>
                                </div>
                            </div>
                            <div
                                class="h-1 bg-gradient-to-r from-blue-600 to-blue-400"
                            ></div>
                        </div>

                        <!-- Total Cabang Card -->
                        <div
                            class="group overflow-hidden rounded-xl border border-slate-200 shadow-sm transition-all duration-300 hover:shadow-lg"
                        >
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p
                                            class="mb-1 text-xs font-semibold tracking-wider uppercase md:text-sm"
                                        >
                                            Total Cabang
                                        </p>
                                        <p
                                            class="text-4xl font-bold tracking-tight text-blue-600 md:text-4xl"
                                        >
                                            {{ overview.totalBranches }}
                                        </p>
                                    </div>
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-xl bg-blue-50 transition-transform duration-300 group-hover:scale-105 dark:bg-blue-900/20"
                                    >
                                        <Building
                                            class="h-7 w-7 text-blue-600"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div
                                class="h-1 bg-gradient-to-r from-blue-600 to-blue-400"
                            ></div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div
                        class="grid grid-cols-1 gap-5 transition-all delay-200 duration-700 ease-out md:gap-6 lg:grid-cols-3"
                        :class="
                            animate
                                ? 'translate-y-0 opacity-100'
                                : 'translate-y-8 opacity-0'
                        "
                    >
                        <!-- Pending Memo Chart -->
                        <div
                            class="overflow-hidden rounded-xl border border-slate-200 shadow-sm"
                        >
                            <div class="border-b border-slate-100 px-5 py-4">
                                <div
                                    class="mb-1 flex items-center justify-between"
                                >
                                    <h3 class="text-base font-bold md:text-lg">
                                        <Link :href="memos.index().url">
                                            Pending Memo
                                        </Link>
                                    </h3>
                                    <span
                                        class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 md:text-sm"
                                    >
                                        {{ pendingMemo.total }}
                                    </span>
                                </div>
                                <p class="text-xs md:text-sm">
                                    Status memo yang belum selesai
                                </p>
                            </div>
                            <div class="p-5 md:p-6">
                                <div class="relative h-52 md:h-56">
                                    <Doughnut
                                        :data="pendingMemoChartData"
                                        :options="pendingMemoChartOptions"
                                    />
                                    <div
                                        v-if="!hasPendingMemoData"
                                        class="absolute inset-0 flex items-center justify-center"
                                    >
                                        <p class="text-sm font-medium">
                                            Tidak ada data
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Matter Chart -->
                        <div
                            class="overflow-hidden rounded-xl border border-slate-200 shadow-sm"
                        >
                            <div class="border-b border-slate-100 px-5 py-4">
                                <div
                                    class="mb-1 flex items-center justify-between"
                                >
                                    <h3 class="text-base font-bold md:text-lg">
                                        <Link :href="tasks.index().url">
                                            Pending Matter
                                        </Link>
                                    </h3>
                                    <span
                                        class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 md:text-sm"
                                    >
                                        {{ pendingMatter.total }}
                                    </span>
                                </div>
                                <p class="text-xs md:text-sm">
                                    Pending matter yang belum selesai
                                </p>
                            </div>
                            <div class="p-5 md:p-6">
                                <div class="relative h-52 md:h-56">
                                    <Doughnut
                                        :data="pendingMatterChartData"
                                        :options="pendingMatterChartOptions"
                                    />
                                    <div
                                        v-if="!hasPendingMatterData"
                                        class="absolute inset-0 flex items-center justify-center"
                                    >
                                        <p class="text-sm font-medium">
                                            Tidak ada data
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Debitur Menabung Chart -->
                        <div
                            class="overflow-hidden rounded-xl border border-slate-200 shadow-sm"
                        >
                            <div class="border-b border-slate-100 px-5 py-4">
                                <div
                                    class="mb-1 flex items-center justify-between"
                                >
                                    <h3 class="text-base font-bold md:text-lg">
                                        <Link :href="debtorSavings.index().url">
                                            Debitur Menabung
                                        </Link>
                                    </h3>
                                    <span
                                        class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 md:text-sm"
                                    >
                                        {{ debiturMenabung.total }}
                                    </span>
                                </div>
                                <p class="text-xs md:text-sm">
                                    Status debitur menabung
                                </p>
                            </div>
                            <div class="p-5 md:p-6">
                                <div class="relative h-52 md:h-56">
                                    <Doughnut
                                        :data="debiturMenabungChartData"
                                        :options="debiturMenabungChartOptions"
                                    />
                                    <div
                                        v-if="!hasDebiturMenabungData"
                                        class="absolute inset-0 flex items-center justify-center"
                                    >
                                        <p class="text-sm font-medium">
                                            Tidak ada data
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped></style>
