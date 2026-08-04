import { router } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import type { TableFilters } from '@/types/pagination';

const SEARCH_DEBOUNCE_MS = 300;

/**
 * Menjaga filter tabel tetap sinkron dengan query string. Perubahan dikirim
 * sebagai kunjungan Inertia parsial supaya halaman tidak berkedip.
 *
 * @see docs/07-admin-ui-standards.md bagian 3
 */
export function useTableFilters(initial: TableFilters, only: string[] = []) {
    const [search, setSearch] = useState(initial.search ?? '');
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const isFirstRun = useRef(true);

    const visit = useCallback(
        (next: TableFilters) => {
            const merged: TableFilters = { ...initial, ...next };

            // Nilai kosong dibuang agar URL tidak penuh parameter tak berguna.
            const params = Object.fromEntries(
                Object.entries(merged).filter(
                    ([, value]) =>
                        value !== null && value !== undefined && value !== '',
                ),
            );

            router.get(window.location.pathname, params, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: only.length > 0 ? only : undefined,
            });
        },
        [initial, only],
    );

    // Pencarian di-debounce 300 ms; halaman selalu kembali ke 1.
    useEffect(() => {
        if (isFirstRun.current) {
            isFirstRun.current = false;

            return;
        }

        if (debounceRef.current) {
            clearTimeout(debounceRef.current);
        }

        debounceRef.current = setTimeout(() => {
            visit({ search, page: 1 });
        }, SEARCH_DEBOUNCE_MS);

        return () => {
            if (debounceRef.current) {
                clearTimeout(debounceRef.current);
            }
        };
        // `visit` sengaja tidak diikutkan: identitasnya berubah tiap render
        // karena `initial` datang dari props Inertia.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const setFilter = useCallback(
        (key: string, value: TableFilters[string]) => {
            visit({ [key]: value, page: 1 });
        },
        [visit],
    );

    const setSort = useCallback(
        (column: string) => {
            const isSame = initial.sort_by === column;
            const nextDir =
                isSame && initial.sort_dir === 'asc' ? 'desc' : 'asc';

            visit({ sort_by: column, sort_dir: nextDir, page: 1 });
        },
        [initial.sort_by, initial.sort_dir, visit],
    );

    const setPage = useCallback((page: number) => visit({ page }), [visit]);

    const setPerPage = useCallback(
        (perPage: number) => visit({ per_page: perPage, page: 1 }),
        [visit],
    );

    const reset = useCallback(() => {
        setSearch('');
        router.get(
            window.location.pathname,
            {},
            { preserveScroll: true, replace: true },
        );
    }, []);

    const hasActiveFilters = Object.entries(initial).some(
        ([key, value]) =>
            !['sort_by', 'sort_dir', 'per_page', 'page'].includes(key) &&
            value !== null &&
            value !== undefined &&
            value !== '',
    );

    return {
        search,
        setSearch,
        setFilter,
        setSort,
        setPage,
        setPerPage,
        reset,
        hasActiveFilters,
    };
}
