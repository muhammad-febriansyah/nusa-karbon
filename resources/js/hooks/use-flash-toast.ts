import { router } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import type { FlashToast } from '@/types/ui';

export function useFlashToast(): void {
    useEffect(() => {
        return router.on('flash', (event) => {
            const flash = (event as CustomEvent).detail?.flash;
            const data = flash?.toast as FlashToast | undefined;

            if (!data) {
                return;
            }

            // Error bertahan lebih lama dan bisa ditutup manual.
            toast[data.type](data.message, {
                duration: data.type === 'error' ? 6000 : 4000,
                closeButton: data.type === 'error',
            });
        });
    }, []);
}
