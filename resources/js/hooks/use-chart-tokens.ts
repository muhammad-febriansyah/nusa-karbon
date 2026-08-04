import { useSyncExternalStore } from 'react';

/**
 * Token warna yang boleh dipakai grafik. Namanya sengaja sama dengan token
 * desain supaya grafik ikut berubah saat tema diganti — ApexCharts butuh nilai
 * warna nyata, bukan `var(--color-*)`, jadi nilainya dibaca dari computed style.
 *
 * @see docs/10-theming.md bagian 2
 */
const TOKENS = [
    'primary',
    'info',
    'success',
    'warning',
    'neutral',
    'destructive',
    'foreground',
    'muted',
    'muted-foreground',
    'border',
    'card',
] as const;

export type ChartToken = (typeof TOKENS)[number];

export type ChartTokens = Record<ChartToken, string>;

/** Dipakai saat SSR/hidrasi pertama, sebelum computed style bisa dibaca. */
const FALLBACK: ChartTokens = {
    primary: '#f93e00',
    info: '#1d4ed8',
    success: '#15803d',
    warning: '#b45309',
    neutral: '#4b5563',
    destructive: '#b91c1c',
    foreground: '#26221f',
    muted: '#f4f3f0',
    'muted-foreground': '#6e6b66',
    border: '#ecebe7',
    card: '#ffffff',
};

let snapshot: ChartTokens = FALLBACK;
let hasRead = false;

function read(): ChartTokens {
    const styles = getComputedStyle(document.documentElement);
    const next = {} as ChartTokens;

    for (const token of TOKENS) {
        next[token] =
            styles.getPropertyValue(`--color-${token}`).trim() ||
            FALLBACK[token];
    }

    return next;
}

function isSame(a: ChartTokens, b: ChartTokens): boolean {
    return TOKENS.every((token) => a[token] === b[token]);
}

/**
 * Editor tema menulis token ke `style` elemen root, jadi perubahan pratinjau
 * cukup dipantau lewat MutationObserver — tidak ada polling.
 */
function subscribe(onChange: () => void): () => void {
    const observer = new MutationObserver(() => {
        const next = read();

        if (!isSame(snapshot, next)) {
            snapshot = next;
            onChange();
        }
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['style', 'class'],
    });

    const next = read();

    if (!isSame(snapshot, next)) {
        snapshot = next;
        onChange();
    }

    return () => observer.disconnect();
}

/**
 * Dibaca sekali sebelum render pertama supaya grafik tidak sempat memakai warna
 * cadangan lalu berkedip ke warna tema.
 */
function getSnapshot(): ChartTokens {
    if (!hasRead && typeof document !== 'undefined') {
        snapshot = read();
        hasRead = true;
    }

    return snapshot;
}

/** Warna tema terkini untuk grafik; ikut ter-render ulang saat tema berubah. */
export function useChartTokens(): ChartTokens {
    return useSyncExternalStore(subscribe, getSnapshot, () => FALLBACK);
}
