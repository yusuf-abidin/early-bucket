<script setup lang="ts">
import {
    Dialog,
    DialogTitle,
    DialogContent,
    DialogHeader,
} from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Area, Branch, Regional } from '@/types';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { createWhatsappLink } from '@/lib/utils';

const selectedRegion = defineModel<{
    regional: Regional | null;
    area: Area | null;
    branch: Branch | null;
} | null>('selected-region', {
    default: null,
});

const getInitials = (name: string) =>
    name
        .split(' ')
        .slice(0, 2)
        .map((w) => w[0])
        .join('')
        .toUpperCase();

watch(selectedRegion, (newRegion) => {
    if (
        newRegion?.regional &&
        newRegion.area == null &&
        newRegion.branch == null
    ) {
        isOpen.value = true;
    } else {
        isOpen.value = false;
    }
}, {
    deep: true,
});

const closeModal = () => {
    selectedRegion.value = null;
    isOpen.value = false;
};

const handleClickArea = (area: Area) => {
    if (!selectedRegion.value) return;
    selectedRegion.value.area = area;
};

const handleClickBranch = (branch: Branch) => {
    if (!selectedRegion.value) return;
    selectedRegion.value.branch = branch;
}

const regional = computed(() => selectedRegion.value?.regional ?? null);

const isOpen = ref(false);
</script>

<template>
    <Dialog v-model:open="isOpen" @update:open="closeModal">
        <DialogContent
            :aria-describedby="undefined"
            class="max-h-[calc(100vh-4rem)] max-w-xl overflow-y-auto p-0 sm:max-w-2xl lg:max-w-xl"
        >
            <ScrollArea>
                <DialogHeader class="px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="mb-1 flex items-center gap-2">
                                <Badge
                                    variant="outline"
                                    class="border-blue-200 bg-blue-50 text-[10px] font-medium tracking-wider text-blue-600 uppercase"
                                >
                                    Regional
                                </Badge>
                            </div>
                            <DialogTitle
                                class="text-lg leading-tight font-medium"
                            >
                                {{ regional?.name }}
                            </DialogTitle>
                        </div>
                    </div>
                </DialogHeader>
                <Separator />

                <div class="px-6 py-4">
                    <p
                        class="mb-3 text-[10px] font-medium tracking-widest text-muted-foreground uppercase"
                    >
                        RLQH
                    </p>
                    <div class="flex items-center gap-3" v-if="regional?.contact_cluster">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-50"
                        >
                            <span class="text-[11px] font-medium text-blue-700">
                                {{
                                    getInitials(
                                        regional?.contact_cluster?.name || '-',
                                    )
                                }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm leading-tight font-medium">
                                {{ regional?.contact_cluster?.name }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                NIP {{ regional?.contact_cluster?.nip || '-'}} ·
                                <a :href="createWhatsappLink(regional?.contact_cluster?.phone || null)" target="_blank" class="hover:underline">
                                    {{ regional?.contact_cluster?.phone }}
                                </a>
                            </p>
                        </div>
                    </div>
                    <div v-else>-</div>
                </div>

                <Separator />

                <div v-if="regional?.areas?.length" class="px-6 py-4">
                    <p
                        class="mb-3 text-[10px] font-medium tracking-widest text-muted-foreground uppercase"
                    >
                        Area ({{ regional.areas.length }})
                    </p>
                    <div class="flex flex-col gap-3">
                        <div
                            v-for="area in regional.areas"
                            :key="area.id"
                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-border px-3 py-2.5 transition-colors hover:bg-muted/50"
                            @click="handleClickArea(area)"
                        >
                            <div
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-50"
                            >
                                <span
                                    class="text-[9px] font-medium text-emerald-700"
                                >
                                    {{ getInitials(area.name) }}
                                </span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm leading-tight font-medium"
                                >
                                    {{ area.name }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    ALQH: {{ area.contact_cluster?.name }} ·
                                    <a :href="createWhatsappLink(area.contact_cluster?.phone || null)"
                                       target="_blank"
                                       class="hover:underline"
                                       @click.stop
                                    >
                                        {{ area.contact_cluster?.phone }}
                                    </a>
                                </p>
                            </div>
                            <Badge
                                variant="outline"
                                class="shrink-0 border-emerald-200 bg-emerald-50 text-[10px] text-emerald-600"
                            >
                                {{ area.branches?.length ?? 0 }} cluster
                            </Badge>
                        </div>
                    </div>
                </div>

                <Separator
                    v-if="regional?.areas?.length && regional?.branches?.length"
                />

                <div v-if="regional?.branches?.length" class="px-6 py-4">
                    <div class="mb-3 flex items-center gap-2">
                        <p
                            class="text-[10px] font-medium tracking-widest text-muted-foreground uppercase"
                        >
                            Cluster
                        </p>
                    </div>
                    <div class="flex flex-col gap-3">
                        <div
                            v-for="cluster in regional.branches"
                            :key="cluster.id"
                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-border px-3 py-2.5 transition-colors hover:bg-muted/50"
                            @click="handleClickBranch(cluster)"
                        >
                            <div
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-violet-50"
                            >
                                <span
                                    class="text-[9px] font-medium text-violet-700"
                                >
                                    {{ getInitials(cluster.name ?? '-') }}
                                </span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm leading-tight font-medium"
                                >
                                    {{ cluster.name }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    CLQH: {{ cluster.contact_cluster?.name }} ·
                                    <a
                                        :href="createWhatsappLink(cluster.contact_cluster?.phone || null)"
                                        target="_blank"
                                        class="hover:underline"
                                        @click.stop
                                    >
                                        {{ cluster.contact_cluster?.phone }}
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </ScrollArea>
        </DialogContent>
    </Dialog>
</template>

<style scoped></style>
