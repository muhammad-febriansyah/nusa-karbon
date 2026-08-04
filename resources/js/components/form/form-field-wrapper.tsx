import type { ReactNode } from 'react';
import { useId } from 'react';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type Props = {
    label: string;
    /** Dipanggil dengan id dan atribut aksesibilitas yang sudah dirakit. */
    children: (field: {
        id: string;
        'aria-describedby'?: string;
        'aria-invalid': boolean;
    }) => ReactNode;
    helper?: string;
    error?: string;
    required?: boolean;
    className?: string;
};

/**
 * Membungkus label, input, helper, dan error sekaligus merangkai
 * `aria-describedby` serta `aria-invalid`.
 *
 * @see docs/07-admin-ui-standards.md bagian 9
 */
export default function FormFieldWrapper({
    label,
    children,
    helper,
    error,
    required = false,
    className,
}: Props) {
    const id = useId();
    const helperId = `${id}-helper`;
    const errorId = `${id}-error`;

    const describedBy =
        [error ? errorId : null, helper ? helperId : null]
            .filter(Boolean)
            .join(' ') || undefined;

    return (
        <div className={cn('grid gap-1.5', className)}>
            <Label htmlFor={id}>
                {label}
                {required && (
                    <span aria-hidden className="text-destructive">
                        *
                    </span>
                )}
            </Label>

            {children({
                id,
                'aria-describedby': describedBy,
                'aria-invalid': Boolean(error),
            })}

            {helper && (
                <p id={helperId} className="text-xs text-muted-foreground">
                    {helper}
                </p>
            )}

            {error && (
                <p id={errorId} className="text-xs text-destructive">
                    {error}
                </p>
            )}
        </div>
    );
}
