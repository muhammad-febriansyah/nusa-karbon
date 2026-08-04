<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Certificate;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Post;
use App\Models\Product;
use App\Models\Representative;
use App\Models\RfqRequest;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pencarian cepat lintas modul untuk kolom cari di header admin.
 *
 * Sengaja memakai `LIKE` biasa, bukan indeks pencarian terpisah: jumlah baris
 * tiap modul masih ratusan, dan menambah infrastruktur indeks untuk itu hanya
 * menambah bagian yang bisa basi tanpa manfaat yang terasa.
 */
final readonly class AdminSearchService
{
    /** Maksimum hasil per kelompok agar daftar tetap bisa dipindai mata. */
    private const PER_GROUP = 5;

    private const MIN_LENGTH = 2;

    /**
     * @return array<int, array{
     *     group: string,
     *     items: array<int, array{id: int, title: string, subtitle: string|null, url: string}>
     * }>
     */
    public function search(string $term): array
    {
        $term = trim($term);

        if (mb_strlen($term) < self::MIN_LENGTH) {
            return [];
        }

        $locale = Locale::default();

        $groups = [
            'Produk' => $this->products($term, $locale),
            'RFQ' => $this->rfqs($term),
            'Artikel' => $this->posts($term, $locale),
            'FAQ' => $this->faqs($term, $locale),
            'Sertifikat' => $this->certificates($term, $locale),
            'Perwakilan' => $this->representatives($term),
            'Pesan Kontak' => $this->contactMessages($term),
        ];

        $result = [];

        foreach ($groups as $group => $items) {
            if ($items !== []) {
                $result[] = ['group' => $group, 'items' => $items];
            }
        }

        return $result;
    }

    /**
     * @return array<int, array{id: int, title: string, subtitle: string|null, url: string}>
     */
    private function products(string $term, string $locale): array
    {
        return Product::query()
            ->where(fn (Builder $query) => $query
                ->where('code', 'like', "%{$term}%")
                ->orWhere("name->{$locale}", 'like', "%{$term}%"))
            ->limit(self::PER_GROUP)
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'title' => $product->getTranslation('name', $locale),
                'subtitle' => $product->code,
                'url' => route('admin.products.show', $product),
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, title: string, subtitle: string|null, url: string}>
     */
    private function rfqs(string $term): array
    {
        return RfqRequest::query()
            ->where(fn (Builder $query) => $query
                ->where('reference_no', 'like', "%{$term}%")
                ->orWhere('company_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%"))
            ->latest()
            ->limit(self::PER_GROUP)
            ->get()
            ->map(fn (RfqRequest $rfq): array => [
                'id' => $rfq->id,
                'title' => $rfq->company_name,
                'subtitle' => $rfq->reference_no,
                'url' => route('admin.rfqs.show', $rfq),
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, title: string, subtitle: string|null, url: string}>
     */
    private function posts(string $term, string $locale): array
    {
        return Post::query()
            ->where("title->{$locale}", 'like', "%{$term}%")
            ->limit(self::PER_GROUP)
            ->get()
            ->map(fn (Post $post): array => [
                'id' => $post->id,
                'title' => $post->getTranslation('title', $locale),
                'subtitle' => $post->status->label(),
                'url' => route('admin.posts.edit', $post),
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, title: string, subtitle: string|null, url: string}>
     */
    private function faqs(string $term, string $locale): array
    {
        return Faq::query()
            ->where("question->{$locale}", 'like', "%{$term}%")
            ->limit(self::PER_GROUP)
            ->get()
            ->map(fn (Faq $faq): array => [
                'id' => $faq->id,
                'title' => $faq->getTranslation('question', $locale),
                'subtitle' => null,
                'url' => route('admin.faqs.edit', $faq),
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, title: string, subtitle: string|null, url: string}>
     */
    private function certificates(string $term, string $locale): array
    {
        return Certificate::query()
            ->where(fn (Builder $query) => $query
                ->where("name->{$locale}", 'like', "%{$term}%")
                ->orWhere('issuer', 'like', "%{$term}%")
                ->orWhere('certificate_number', 'like', "%{$term}%"))
            ->limit(self::PER_GROUP)
            ->get()
            ->map(fn (Certificate $certificate): array => [
                'id' => $certificate->id,
                'title' => $certificate->getTranslation('name', $locale),
                'subtitle' => $certificate->issuer,
                'url' => route('admin.certificates.edit', $certificate),
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, title: string, subtitle: string|null, url: string}>
     */
    private function representatives(string $term): array
    {
        return Representative::query()
            ->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%"))
            ->limit(self::PER_GROUP)
            ->get()
            ->map(fn (Representative $representative): array => [
                'id' => $representative->id,
                'title' => $representative->name,
                'subtitle' => $representative->email,
                'url' => route('admin.representatives.edit', $representative),
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, title: string, subtitle: string|null, url: string}>
     */
    private function contactMessages(string $term): array
    {
        return ContactMessage::query()
            ->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('subject', 'like', "%{$term}%"))
            ->latest()
            ->limit(self::PER_GROUP)
            ->get()
            ->map(fn (ContactMessage $message): array => [
                'id' => $message->id,
                'title' => $message->name,
                'subtitle' => $message->subject,
                'url' => route('admin.contact-messages.index', ['search' => $message->email]),
            ])
            ->all();
    }
}
