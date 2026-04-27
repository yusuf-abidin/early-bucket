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

const badgeColor = {
    Merah: 'bg-red-500/80 text-red-900 inset-ring inset-ring-red-400/20 dark:text-gray-900',
    Oranye: 'bg-orange-400/80 text-orange-900 inset-ring inset-ring-orange-400/20 dark:text-gray-900',
    Kuning: 'bg-yellow-400/80 text-yellow-900 inset-ring inset-ring-yellow-400/30 dark:text-gray-900',
    'Hijau Muda':
        'bg-lime-400/80 text-lime-900 inset-ring inset-ring-lime-400/30 dark:text-gray-900',
    Hijau: 'bg-green-400/80 text-green-800 inset-ring inset-ring-green-500/20 dark:text-gray-900',
    'Biru Langit':
        'bg-cyan-400/80 text-cyan-900 inset-ring inset-ring-cyan-400/20 dark:text-gray-900',
    Biru: 'bg-blue-400/80 text-blue-900 inset-ring inset-ring-blue-400/30 dark:text-gray-900',
    'Merah Muda':
        'bg-pink-400/80 text-pink-900 inset-ring inset-ring-pink-400/20 dark:text-gray-900',
    Ungu: 'bg-purple-400/80 text-purple-900 inset-ring inset-ring-purple-400/30 dark:text-gray-900',
    'Abu-Abu': 'bg-gray-300/80 text-gray-900 inset-ring inset-ring-gray-500/10',
} as const;

export const getBadgeColor = (name: string): string => {
    const color = badgeColor[name as keyof typeof badgeColor];
    return color ?? badgeColor['Abu-Abu'];
};


export const df = new DateFormatter('id-ID', {
    dateStyle: 'medium',
});

export const createWhatsappLink = (
    phone: string | null,
    message?: string,
): string | undefined => {
    if (!phone) return undefined;
    let cleaned = phone.replace(/\D/g, '');

    const isValid = /^(?:62|0)?8\d{7,12}$/.test(cleaned);
    if (!isValid) return undefined;

    if (cleaned.startsWith('62')) {
        // sudah benar
    } else if (cleaned.startsWith('0')) {
        cleaned = '62' + cleaned.slice(1);
    } else if (cleaned.startsWith('8')) {
        cleaned = '62' + cleaned;
    }
    const baseUrl = `https://wa.me/${cleaned}`;
    if (!message) return baseUrl;
    return `${baseUrl}?text=${encodeURIComponent(message)}`;
};
