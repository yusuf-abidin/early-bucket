import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { DateFormatter } from '@internationalized/date';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    return toUrl(urlToCheck) === currentUrl;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export const getBadgeColor = (name: string) => {
    const colors = [
        'bg-blue-100 text-blue-700 border-blue-200',
        'bg-emerald-100 text-emerald-700 border-emerald-200',
        'bg-violet-100 text-violet-700 border-violet-200',
        'bg-amber-100 text-amber-700 border-amber-200',
        'bg-rose-100 text-rose-700 border-rose-200',
        'bg-cyan-100 text-cyan-700 border-cyan-200',
        'bg-orange-100 text-orange-700 border-orange-200',
        'bg-indigo-100 text-indigo-700 border-indigo-200',
    ];

    let hash = 0;

    for (let i = 0; i < name.length; i++) {
        // Setiap karakter diberi bobot berdasarkan posisinya
        hash += name.charCodeAt(i) * (i + 1) * 37;
    }

    const index = Math.abs(hash) % colors.length;
    return colors[index];
};

export const df = new DateFormatter('id-ID', {
    dateStyle: 'medium',
});
