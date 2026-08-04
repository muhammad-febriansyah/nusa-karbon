/**
 * Bentuk keluaran `LengthAwarePaginator` yang dibungkus API Resource Laravel.
 */
export type PaginatedMeta = {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
};

export type Paginated<TData> = {
    data: TData[];
    meta: PaginatedMeta;
};

/**
 * Filter tabel yang disinkronkan ke query string agar bisa di-bookmark dan
 * bertahan setelah refresh.
 *
 * @see docs/07-admin-ui-standards.md bagian 3
 */
export type TableFilters = {
    search?: string;
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
    [key: string]: string | number | boolean | null | undefined;
};
