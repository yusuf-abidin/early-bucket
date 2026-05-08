<script setup lang="ts">
import { Branch, Regional } from '@/types';
import { OrgNode, transformToOrgNodes } from '@/lib/orgChartTransform';
import { OrgChart } from 'd3-org-chart';
import { onMounted, ref, watch } from 'vue';

const props = defineProps<{
    regional: Regional;
    activeBranchId?: number | null;
}>();

const emit = defineEmits<{
    (
        e: 'edit-contact',
        payload: { type: 'regional' | 'area' | 'branch'; raw: any },
    ): void;
    (e: 'select-branch', branch: Branch | null): void;
}>();

const chartContainer = ref<HTMLElement | null>(null);
let chart: OrgChart<OrgNode> | null = null;

// UI Constants moved outside for better structure
const accentClass: Record<string, string> = {
    regional: 'bg-blue-700',
    area: 'bg-teal-600',
    branch: 'bg-yellow-400',
};

const badgeClass: Record<string, string> = {
    regional: 'bg-blue-100 text-blue-800',
    area: 'bg-teal-100 text-teal-800',
    branch: 'bg-yellow-200 text-yellow-700',
};

const badgeLabel: Record<string, string> = {
    regional: 'RLQH',
    area: 'ALQH',
    branch: 'CLQH',
};

const initChart = () => {
    if (!chartContainer.value) return;

    chart = new OrgChart<OrgNode>()
        .container(chartContainer.value as any) // Cast to any because library types expect string selector
        .nodeId((d: any) => d.id) // Use any to avoid union type issues in HierarchyNode
        .parentNodeId((d: any) => d.parentId)
        .nodeWidth(() => 260)
        .nodeHeight((d: any) => (d.data.type === 'branch' ? 140 : 110))
        .compact(true)
        .linkUpdate(function (d: any, i: number, arr: any[]) {
            const path = arr[i] as SVGPathElement;
            path.setAttribute('stroke-width', '2');
            path.setAttribute('stroke', '#94a3b8');
        })
        .nodeContent((d: any) => {
            const node = d.data as OrgNode;
            const contact = (node.raw as any)?.contact_cluster;
            const nip = contact?.nip ?? '-';
            const photo = `https://i.pravatar.cc/80?img=${node.raw.id ?? 1}`;
            const phone = contact?.phone;
            const waLink = phone
                ? `https://wa.me/${phone.replace(/\D/g, '')}`
                : null;

            const avatarHtml = contact
                ? `<img src="${photo}" class="w-14 h-14 rounded-full object-cover shrink-0 border border-slate-200" />`
                : `<div class="w-14 h-14 rounded-full shrink-0 bg-slate-100 flex items-center justify-center text-xl text-slate-400">&#128100;</div>`;

            const phoneHtml = phone
                ? `
                <div class="flex items-center gap-1 mt-0.5">
                    <a href="${waLink}" target="_blank" class="flex items-center gap-1 text-slate-600 hover:text-emerald-600 transition-colors no-underline">
                        <svg class="h-3 w-3 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        <span class="text-[11px] font-medium truncate">${phone}</span>
                    </a>
                    <button class="node-phone-copy-btn p-1 rounded hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-all cursor-pointer border-none bg-transparent"
                            data-phone="${phone}">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                </div>
            `
                : '<span class="text-[11px] text-slate-400 mt-0.5">-</span>';

            const isActive =
                node.type === 'branch' && node.raw.id === props.activeBranchId;
            const toggleBtnHtml =
                node.type === 'branch'
                    ? `
                <button class="node-toggle-stc-btn w-full py-1 cursor-pointer text-[10px] font-bold uppercase rounded border transition-colors ${isActive ? 'bg-slate-100 text-slate-700 border-slate-300' : 'bg-blue-600 text-white border-blue-700 hover:bg-blue-700'}"
                        data-id="${node.raw.id}">
                    ${isActive ? 'Tutup STC & TL' : 'Buka STC & TL'}
                </button>
            `
                    : '';

            return `
        <div class="flex items-center gap-3 bg-white border border-slate-200 rounded p-3 w-full h-full box-border relative overflow-hidden font-sans group cursor-default">
            <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l ${accentClass[node.type]}"></div>

            <button class="node-edit-btn absolute top-1 right-1 p-1.5 rounded-full bg-slate-50 text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition-all opacity-0 group-hover:opacity-100 cursor-pointer border border-slate-200"
                    data-id="${node.id}">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
            </button>

            ${avatarHtml}
            <div class="flex flex-col gap-0 min-w-0 flex-1 h-full">
                <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded w-fit tracking-wider uppercase ${badgeClass[node.type]}">
                    ${badgeLabel[node.type]}
                </span>
                <span class="text-sm font-bold text-slate-900 truncate leading-tight" title="${node.name ?? ''}">${node.name ?? '-'}</span>
                <span class="text-xs font-medium text-slate-700 truncate" title="${contact?.name ?? ''}">${contact?.name ?? '-'}</span>
                ${phoneHtml}
                <span class="text-[10px] text-slate-500 mb-1">NIP: ${nip}</span>
                ${toggleBtnHtml}
            </div>
        </div>
    `;
        })
        .scaleExtent([0.55, 1])
        .compactMarginPair(() => 40)
        .neighbourMargin(() => 20)
        .childrenMargin(() => 40)
        .siblingsMargin(() => 15);
};

const updateChart = () => {
    if (!props.regional || !chart) return;

    const data = transformToOrgNodes(props.regional);
    chart.data(data).render().expandAll().fit();
};

onMounted(() => {
    initChart();
    updateChart();

    chartContainer.value?.addEventListener('click', (e) => {
        const target = e.target as HTMLElement;

        // Handle Edit Button
        const editBtn = target.closest('.node-edit-btn');
        if (editBtn) {
            e.stopPropagation();
            const id = editBtn.getAttribute('data-id');
            const data = transformToOrgNodes(props.regional);
            const node = data.find((n) => n.id === id);
            if (node) {
                emit('edit-contact', { type: node.type, raw: node.raw });
            }
            return;
        }

        // Handle Phone Copy Button
        const copyBtn = target.closest('.node-phone-copy-btn');
        if (copyBtn) {
            e.stopPropagation();
            const phone = copyBtn.getAttribute('data-phone');
            if (phone) {
                navigator.clipboard.writeText(phone);

                const originalHtml = copyBtn.innerHTML;
                copyBtn.innerHTML =
                    '<svg class="h-3 w-3 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
                setTimeout(() => {
                    copyBtn.innerHTML = originalHtml;
                }, 2000);
            }
            return;
        }

        // Handle Toggle STC Button
        const toggleBtn = target.closest('.node-toggle-stc-btn');
        if (toggleBtn) {
            e.stopPropagation();
            const id = Number(toggleBtn.getAttribute('data-id'));
            const data = transformToOrgNodes(props.regional);
            const node = data.find(
                (n) => n.type === 'branch' && n.raw.id === id,
            );
            if (node) {
                const isCurrentlyActive = props.activeBranchId === id;
                emit(
                    'select-branch',
                    isCurrentlyActive ? null : (node.raw as Branch),
                );
            }
            return;
        }
    });
});

watch(() => props.regional, updateChart, { deep: true });
watch(() => props.activeBranchId, updateChart, { deep: true });
</script>

<template>
    <div
        ref="chartContainer"
        class="chart-container max-h-[calc(100vh-6rem)]"
    />
</template>

<style scoped>
.chart-container {
    width: 100%;
    background-color: #f9f9f9;
}
</style>
