import { Area, Branch, Regional } from '@/types';

export interface OrgNode {
    id: string;
    parentId: string | null;
    name: string;
    type: 'regional' | 'area' | 'branch';
    contact?: string;
    position?: string;
    phone?: string;
    nip?: string;
    raw: Regional | Area | Branch;
}

export function transformToOrgNodes(regional: Regional): OrgNode[] {
    const nodes: OrgNode[] = [];

    // Node Regional (root)
    const regionalId = `regional-${regional.id}`;
    nodes.push({
        id: regionalId,
        parentId: null,
        name: regional.name,
        type: 'regional',
        contact: regional.contact_cluster?.name,
        position: regional.contact_cluster?.position,
        phone: regional.contact_cluster?.phone,
        nip: regional.contact_cluster?.nip,
        raw: regional,
    });

    // Node Area
    for (const area of regional.areas) {
        const areaId = `area-${area.id}`;
        nodes.push({
            id: areaId,
            parentId: regionalId,
            name: area.name,
            type: 'area',
            contact: area.contact_cluster?.name,
            position: area.contact_cluster?.position,
            phone: area.contact_cluster?.phone,
            nip: area.contact_cluster?.nip,
            raw: area,
        });

        // Branch di bawah Area
        for (const branch of area.branches) {
            nodes.push({
                id: `branch-${branch.id}`,
                parentId: areaId,
                name: branch.name,
                type: 'branch',
                contact: branch.contact_cluster?.name,
                position: branch.contact_cluster?.position,
                phone: branch.contact_cluster?.phone,
                raw: branch,
            });
        }
    }

    // Branch langsung di bawah Regional (area_id === null)
    for (const branch of regional.branches) {
        if (branch.area_id === null) {
            nodes.push({
                id: `branch-${branch.id}`,
                parentId: regionalId,
                name: branch.name,
                type: 'branch',
                contact: branch.contact_cluster?.name,
                position: branch.contact_cluster?.position,
                phone: branch.contact_cluster?.phone,
                raw: branch,
            });
        }
    }

    return nodes;
}
