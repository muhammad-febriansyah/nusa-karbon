import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import {
    Bold,
    Heading2,
    Heading3,
    ImagePlus,
    Italic,
    Link2,
    Link2Off,
    List,
    ListOrdered,
    Quote,
    Redo2,
    Undo2,
    X,
} from 'lucide-react';
import type { ReactNode } from 'react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type Props = {
    value: string;
    onChange: (html: string) => void;
    /** Membuka media library; kembalikan URL gambar terpilih. */
    onPickImage?: () => Promise<string | null>;
    placeholder?: string;
    disabled?: boolean;
    id?: string;
    'aria-invalid'?: boolean;
};

function ToolbarButton({
    active,
    disabled,
    onClick,
    label,
    children,
}: {
    active?: boolean;
    disabled?: boolean;
    onClick: () => void;
    label: string;
    children: ReactNode;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            disabled={disabled}
            title={label}
            aria-pressed={active}
            className={cn(
                'inline-flex size-8 items-center justify-center rounded-[var(--radius)] text-muted-foreground transition-colors outline-none hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:size-4',
                active && 'bg-muted text-foreground',
            )}
        >
            {children}
            <span className="sr-only">{label}</span>
        </button>
    );
}

/**
 * Editor rich text untuk deskripsi produk dan isi artikel.
 * HTML keluarannya wajib disanitasi di server memakai `mews/purifier`.
 *
 * @see docs/09-tasks.md bagian 1.6
 */
export default function RichTextEditor({
    value,
    onChange,
    onPickImage,
    placeholder,
    disabled = false,
    id,
    'aria-invalid': invalid,
}: Props) {
    const [linkDialogOpen, setLinkDialogOpen] = useState(false);
    const [linkUrl, setLinkUrl] = useState('');

    const editor = useEditor({
        editable: !disabled,
        content: value,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
            }),
            Link.configure({ openOnClick: false }),
            Image,
        ],
        editorProps: {
            attributes: {
                class: 'prose-editor min-h-52 w-full px-3 py-2 text-sm outline-none',
                ...(placeholder ? { 'data-placeholder': placeholder } : {}),
            },
        },
        onUpdate: ({ editor }) => onChange(editor.getHTML()),
    });

    if (!editor) {
        return null;
    }

    const toggleLink = () => {
        if (editor.isActive('link')) {
            editor.chain().focus().unsetLink().run();

            return;
        }

        setLinkUrl(String(editor.getAttributes('link').href ?? ''));
        setLinkDialogOpen(true);
    };

    const applyLink = () => {
        const url = linkUrl.trim();

        if (url !== '') {
            editor.chain().focus().setLink({ href: url }).run();
        }

        setLinkDialogOpen(false);
        setLinkUrl('');
    };

    const insertImage = async () => {
        if (!onPickImage) {
            return;
        }

        const url = await onPickImage();

        if (url) {
            editor.chain().focus().setImage({ src: url }).run();
        }
    };

    return (
        <div
            id={id}
            aria-invalid={invalid}
            className="overflow-hidden rounded-[var(--radius)] border border-input aria-invalid:border-destructive"
        >
            <div className="flex flex-wrap items-center gap-0.5 border-b border-border bg-background p-1">
                <ToolbarButton
                    label="Tebal"
                    active={editor.isActive('bold')}
                    onClick={() => editor.chain().focus().toggleBold().run()}
                >
                    <Bold />
                </ToolbarButton>
                <ToolbarButton
                    label="Miring"
                    active={editor.isActive('italic')}
                    onClick={() => editor.chain().focus().toggleItalic().run()}
                >
                    <Italic />
                </ToolbarButton>

                <span aria-hidden className="mx-1 h-5 w-px bg-border" />

                <ToolbarButton
                    label="Judul 2"
                    active={editor.isActive('heading', { level: 2 })}
                    onClick={() =>
                        editor.chain().focus().toggleHeading({ level: 2 }).run()
                    }
                >
                    <Heading2 />
                </ToolbarButton>
                <ToolbarButton
                    label="Judul 3"
                    active={editor.isActive('heading', { level: 3 })}
                    onClick={() =>
                        editor.chain().focus().toggleHeading({ level: 3 }).run()
                    }
                >
                    <Heading3 />
                </ToolbarButton>

                <span aria-hidden className="mx-1 h-5 w-px bg-border" />

                <ToolbarButton
                    label="Daftar berpoin"
                    active={editor.isActive('bulletList')}
                    onClick={() =>
                        editor.chain().focus().toggleBulletList().run()
                    }
                >
                    <List />
                </ToolbarButton>
                <ToolbarButton
                    label="Daftar bernomor"
                    active={editor.isActive('orderedList')}
                    onClick={() =>
                        editor.chain().focus().toggleOrderedList().run()
                    }
                >
                    <ListOrdered />
                </ToolbarButton>
                <ToolbarButton
                    label="Kutipan"
                    active={editor.isActive('blockquote')}
                    onClick={() =>
                        editor.chain().focus().toggleBlockquote().run()
                    }
                >
                    <Quote />
                </ToolbarButton>

                <span aria-hidden className="mx-1 h-5 w-px bg-border" />

                <ToolbarButton
                    label={editor.isActive('link') ? 'Hapus tautan' : 'Tautan'}
                    active={editor.isActive('link')}
                    onClick={toggleLink}
                >
                    {editor.isActive('link') ? <Link2Off /> : <Link2 />}
                </ToolbarButton>

                {onPickImage && (
                    <ToolbarButton label="Sisip gambar" onClick={insertImage}>
                        <ImagePlus />
                    </ToolbarButton>
                )}

                <span aria-hidden className="mx-1 h-5 w-px bg-border" />

                <ToolbarButton
                    label="Urungkan"
                    disabled={!editor.can().undo()}
                    onClick={() => editor.chain().focus().undo().run()}
                >
                    <Undo2 className="rtl:rotate-180" />
                </ToolbarButton>
                <ToolbarButton
                    label="Ulangi"
                    disabled={!editor.can().redo()}
                    onClick={() => editor.chain().focus().redo().run()}
                >
                    <Redo2 className="rtl:rotate-180" />
                </ToolbarButton>
            </div>

            <EditorContent editor={editor} />

            <Dialog open={linkDialogOpen} onOpenChange={setLinkDialogOpen}>
                <DialogContent>
                    <DialogTitle>Sisipkan tautan</DialogTitle>
                    <DialogDescription>
                        Masukkan alamat lengkap beserta protokolnya.
                    </DialogDescription>

                    <div className="grid gap-1.5">
                        <Label htmlFor="rich-text-link">Alamat tautan</Label>
                        <Input
                            id="rich-text-link"
                            dir="ltr"
                            value={linkUrl}
                            onChange={(event) => setLinkUrl(event.target.value)}
                            placeholder="https://nusacarbon.com"
                            onKeyDown={(event) => {
                                if (event.key === 'Enter') {
                                    event.preventDefault();
                                    applyLink();
                                }
                            }}
                        />
                    </div>

                    <DialogFooter className="gap-2">
                        <Button
                            type="button"
                            variant="cancel"
                            onClick={() => setLinkDialogOpen(false)}
                        >
                            <X />
                            Batal
                        </Button>
                        <Button
                            type="button"
                            variant="save"
                            onClick={applyLink}
                            disabled={linkUrl.trim() === ''}
                        >
                            <Link2 />
                            Sisipkan Tautan
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}
