import { cva, type VariantProps } from "class-variance-authority"
import { Slot } from "radix-ui"
import * as React from "react"

import { cn } from "@/lib/utils"

/*
 * Varian bawaan shadcn (default, ghost, outline, secondary, link) sengaja
 * DIHAPUS agar tidak ada jalan pintas membuat tombol transparan.
 * Warna ditentukan jenis aksi, bukan tingkat kepentingan.
 * Referensi: docs/05-design-system.md bagian 3.
 *
 * Hover memakai brightness-110, bukan warna hover terpisah, karena warna
 * berasal dari tema yang bisa diganti admin.
 *
 * CATATAN: `npx/pnpm dlx shadcn add` akan menimpa berkas ini. Bila itu terjadi,
 * kembalikan versi ini — jangan pakai varian bawaan shadcn.
 */
const buttonVariants = cva(
  "inline-flex shrink-0 items-center justify-center gap-2 rounded-[var(--radius)] text-[13px] font-medium whitespace-nowrap transition-[filter,box-shadow] outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
  {
    variants: {
      variant: {
        create: "bg-primary text-primary-foreground hover:brightness-110",
        save: "bg-primary text-primary-foreground hover:brightness-110",
        edit: "bg-success text-success-foreground hover:brightness-110",
        delete:
          "bg-destructive text-destructive-foreground hover:brightness-110",
        detail: "bg-warning text-warning-foreground hover:brightness-110",
        export: "bg-info text-info-foreground hover:brightness-110",
        upload: "bg-info text-info-foreground hover:brightness-110",
        filter: "bg-info text-info-foreground hover:brightness-110",
        passkey: "bg-info text-info-foreground hover:brightness-110",
        duplicate: "bg-violet text-violet-foreground hover:brightness-110",
        toggle: "bg-teal text-teal-foreground hover:brightness-110",
        publish: "bg-teal text-teal-foreground hover:brightness-110",
        cancel: "bg-neutral text-neutral-foreground hover:brightness-110",
        back: "bg-neutral text-neutral-foreground hover:brightness-110",
        reset: "bg-neutral text-neutral-foreground hover:brightness-110",
        archive: "bg-neutral text-neutral-foreground hover:brightness-110",
        whatsapp: "bg-success text-success-foreground hover:brightness-110",
        /*
         * Khusus kontrol di dalam komponen (navigasi bulan pada kalender,
         * tombol tutup dialog). Bukan tombol aksi, jadi tidak melanggar
         * aturan "tanpa tombol transparan".
         */
        control:
          "bg-transparent text-foreground hover:bg-muted aria-selected:bg-primary aria-selected:text-primary-foreground",
      },
      size: {
        sm: "h-8 px-3",
        default: "h-9 px-4",
        lg: "h-10 px-5",
        icon: "size-8 px-0",
      },
    },
    defaultVariants: { variant: "create", size: "default" },
  },
)

function Button({
  className,
  variant,
  size,
  asChild = false,
  ...props
}: React.ComponentProps<"button"> &
  VariantProps<typeof buttonVariants> & {
    asChild?: boolean
  }) {
  const Comp = asChild ? Slot.Root : "button"

  return (
    <Comp
      data-slot="button"
      className={cn(buttonVariants({ variant, size, className }))}
      {...props}
    />
  )
}

export { Button, buttonVariants }
