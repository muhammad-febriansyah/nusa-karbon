import { Link, useForm } from '@inertiajs/react';
import { Save, X } from 'lucide-react';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import {
    index as faqsIndex,
    store as storeFaq,
    update as updateFaq,
} from '@/routes/admin/faqs';
import type { Faq, FaqCategory, Translated } from '@/types/models';

const LOCALE = 'id';

const NO_CATEGORY = 'none';

type Props = {
    faq: Faq | null;
    categories: FaqCategory[];
};

type FaqFormData = {
    faq_category_id: string;
    question: Translated;
    answer: Translated;
    is_active: boolean;
};

const toForm = (faq: Faq | null): FaqFormData => ({
    faq_category_id:
        faq?.faq_category_id === null || faq?.faq_category_id === undefined
            ? NO_CATEGORY
            : String(faq.faq_category_id),
    question: { [LOCALE]: faq?.question?.[LOCALE] ?? '' },
    answer: { [LOCALE]: faq?.answer?.[LOCALE] ?? '' },
    is_active: faq?.is_active ?? true,
});

export default function FaqForm({ faq, categories }: Props) {
    const isEdit = faq !== null;
    const { data, setData, transform, post, put, processing, errors } =
        useForm<FaqFormData>(toForm(faq));

    // Select shadcn tidak menerima nilai kosong, jadi "Tanpa kategori" dikirim
    // ke server sebagai null.
    transform((payload) => ({
        ...payload,
        faq_category_id:
            payload.faq_category_id === NO_CATEGORY
                ? null
                : Number(payload.faq_category_id),
    }));

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        if (isEdit) {
            put(updateFaq(faq.id).url);

            return;
        }

        post(storeFaq().url);
    };

    const questionError = errors[`question.${LOCALE}` as keyof typeof errors];
    const answerError = errors[`answer.${LOCALE}` as keyof typeof errors];

    return (
        <form onSubmit={submit} className="space-y-5">
            <Panel
                title="Pertanyaan & Jawaban"
                description="Tulis seperti buyer bertanya, jawab dengan angka dan fakta yang bisa dicek."
            >
                <div className="grid gap-4">
                    <div className="grid gap-1.5">
                        <Label htmlFor="faq_category_id">Kategori</Label>
                        <Select
                            value={data.faq_category_id}
                            onValueChange={(value) =>
                                setData('faq_category_id', value)
                            }
                        >
                            <SelectTrigger id="faq_category_id">
                                <SelectValue placeholder="Pilih kategori" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={NO_CATEGORY}>
                                    Tanpa kategori (kelompok Lainnya)
                                </SelectItem>
                                {categories.map((category) => (
                                    <SelectItem
                                        key={category.id}
                                        value={String(category.id)}
                                    >
                                        {category.name?.[LOCALE] ??
                                            'Tanpa nama'}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.faq_category_id && (
                            <p className="text-xs text-destructive">
                                {errors.faq_category_id}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="question">
                            Pertanyaan
                            <span aria-hidden className="text-destructive">
                                *
                            </span>
                        </Label>
                        <Input
                            id="question"
                            value={data.question[LOCALE] ?? ''}
                            onChange={(event) =>
                                setData('question', {
                                    [LOCALE]: event.target.value,
                                })
                            }
                            placeholder="Contoh: Berapa MOQ untuk pengiriman pertama?"
                            aria-invalid={Boolean(questionError)}
                        />
                        {questionError && (
                            <p className="text-xs text-destructive">
                                {questionError}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="answer">
                            Jawaban
                            <span aria-hidden className="text-destructive">
                                *
                            </span>
                        </Label>
                        <Textarea
                            id="answer"
                            rows={6}
                            value={data.answer[LOCALE] ?? ''}
                            onChange={(event) =>
                                setData('answer', {
                                    [LOCALE]: event.target.value,
                                })
                            }
                            placeholder="Jawab langsung di kalimat pertama, detail menyusul."
                            aria-invalid={Boolean(answerError)}
                        />
                        {answerError && (
                            <p className="text-xs text-destructive">
                                {answerError}
                            </p>
                        )}
                    </div>
                </div>
            </Panel>

            <Panel title="Status">
                <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                    <div className="space-y-0.5">
                        <Label htmlFor="is_active">Aktif</Label>
                        <p className="text-xs text-muted-foreground">
                            Pertanyaan nonaktif tidak tampil di halaman FAQ.
                        </p>
                    </div>
                    <Switch
                        id="is_active"
                        checked={data.is_active}
                        onCheckedChange={(checked) =>
                            setData('is_active', checked)
                        }
                    />
                </div>
            </Panel>

            <div className="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-border bg-card/95 px-4 py-3.5 backdrop-blur md:-mx-6 md:px-6">
                <Button asChild variant="cancel" type="button">
                    <Link href={faqsIndex()}>
                        <X />
                        Batal
                    </Link>
                </Button>
                <Button
                    type="submit"
                    variant="save"
                    disabled={processing}
                    data-test="save-faq-button"
                >
                    {processing ? <Spinner /> : <Save />}
                    {processing
                        ? 'Menyimpan...'
                        : isEdit
                          ? 'Simpan Perubahan'
                          : 'Simpan FAQ'}
                </Button>
            </div>
        </form>
    );
}
