import { useEffect, useState } from 'react';

/**
 * Menahan perubahan nilai selama `delay` milidetik.
 *
 * Dipakai kolom pencarian supaya tiap ketikan tidak jadi satu permintaan ke
 * server — mengetik "briket" tanpa penahan berarti enam kali query.
 */
export function useDebouncedValue<T>(value: T, delay = 300): T {
    const [debounced, setDebounced] = useState(value);

    useEffect(() => {
        const timer = window.setTimeout(() => setDebounced(value), delay);

        return () => window.clearTimeout(timer);
    }, [value, delay]);

    return debounced;
}
