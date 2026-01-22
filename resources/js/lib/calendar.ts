// resources/js/lib/calendar.ts

export type SimpleDateValue = {
    year: number;
    month: number;
    day: number;

    /** wajib ada */
    toDate(): Date;
    toString(): string;
};

export const calendarHelper = {
    today(): SimpleDateValue {
        return this.fromDate(new Date());
    },

    fromISO(iso: string): SimpleDateValue {
        const [y, m, d] = iso.split('-').map(Number);
        return this.create(y, m, d);
    },

    fromDate(date: Date): SimpleDateValue {
        return this.create(
            date.getFullYear(),
            date.getMonth() + 1,
            date.getDate(),
        );
    },

    create(year: number, month: number, day: number): SimpleDateValue {
        return {
            year,
            month,
            day,

            toDate() {
                return new Date(year, month - 1, day);
            },

            toString() {
                return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            },
        };
    },

    toDisplay(date: SimpleDateValue | null): string {
        if (!date) return '';
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(date.toDate());
    },
};
