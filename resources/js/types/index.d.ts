import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
    items?: {
        title: string;
        href: NonNullable<InertiaLinkProps['href']>;
    }[]
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    flash: FlashMessage;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    position?: string;
    color_id?: number;
    color?: Color;
    role: 'admin' | 'user';
    tasks: Task[] | null;
    email_verified_at: string | null;
    deleted_at: string | null;
    created_at: string;
    updated_at: string;
}


export interface FlashMessage {
    success: string | null;
    error: string | null;
    warning: string | null;
    info: string | null;
    message: string | null;
}

export interface Category {
    id: number;
    name: string;
    type: string;
    order: number;
    color_id?: number;
    color?: Color;
    created_at: string;
    updated_at: string;
}

export interface Color {
    id: number;
    name: string;
    class: string;
}

export interface Task {
    id: number;
    type: string;
    task_description: string;
    category_id: number;
    category: Category | null;
    users: User[] | null;
    due_date: string | null;
    notes: string | null;
    completed_at: string | null;
    created_at: string;
    updated_at: string;
    due_date_updated_at: string;
}

export interface UserSummary extends Pick<User, 'id' | 'name' | 'avatar' | 'position'>{
    pending_count: number;
    overdue_count: number;
    near_overdue_count: number;
    completed_count?: number;
    completed_this_week_count?: number
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface LaravelPaginator<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

export interface Memo {
    id: number;
    received_at: string | null;
    origin: string | null;
    users: User[] | null;
    reference_number: string | null;
    subject: string | null;
    category: Category;
    completed_at: string | null;
    category_id?: number;
    due_date: string | null;
    document_link?: string;
    follow_up_note: string;
}

export interface Regional {
    id: number;
    name: string;
    areas: Area[];
    branches: Branch[];
    contact_cluster?: ContactCluster;
}

export interface Area {
    id: number;
    regional_id: number;
    name: string;
    branches: Branch[];
    contact_cluster?: ContactCluster;
}

export interface Branch {
    id: number;
    regional_id: number;
    area_id: number | null;
    name: string;
    contact_cluster?: ContactCluster;
}

export interface PerformanceEtape {
    id: number;
    branch?: Branch;
    etape_no: number;
    user?: User;
    prognosa_akhir_bulan: string | null;
    kendala: string | null;
    year: number;
    month: number;
}

export interface ContactCluster {
    id: number;
    regional: Regional;
    area: Area;
    branch: Branch;
    name: string;
    nip: string;
    phone: string;
    position: string;
}

export type EditContactPayload = {
    targetType: 'REGIONAL' | 'AREA' | 'BRANCH';
    regional?: Regional;
    area?: Area;
    branch?: Branch;
    contact: ContactCluster | undefined;
};

export type BreadcrumbItemType = BreadcrumbItem;
