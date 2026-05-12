<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import RlqhLayout from '@/layouts/RlqhLayout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Pengaturan tampilan',
        href: edit().url,
    },
];
</script>

<template>
    <RlqhLayout :breadcrumbs="breadcrumbItems">
        <Head title="Pengaturan tampilan" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Pengaturan Tampilan"
                    description="Perbarui pengaturan tampilan akun Anda"
                />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </RlqhLayout>
</template>
