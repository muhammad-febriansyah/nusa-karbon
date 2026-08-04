<?php

declare(strict_types=1);

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\User;
use Database\Seeders\FaqSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('tamu tidak bisa mengakses FAQ', function () {
    $this->get(route('admin.faqs.index'))
        ->assertRedirect(route('login'));
});

test('halaman index memuat FAQ beserta kategorinya', function () {
    $category = FaqCategory::factory()->create();
    Faq::factory()->count(2)->create(['faq_category_id' => $category->id]);
    Faq::factory()->uncategorised()->create();

    $this->actingAs($this->user)
        ->get(route('admin.faqs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/faqs/index')
            ->has('faqs', 3)
            ->has('categories', 1),
        );
});

test('FAQ baru masuk urutan terakhir di kategorinya', function () {
    $first = FaqCategory::factory()->create();
    $second = FaqCategory::factory()->create();

    Faq::factory()->create(['faq_category_id' => $first->id, 'sort_order' => 3]);
    Faq::factory()->create(['faq_category_id' => $second->id, 'sort_order' => 7]);

    $this->actingAs($this->user)
        ->post(route('admin.faqs.store'), [
            'faq_category_id' => $second->id,
            'question' => ['id' => 'Berapa MOQ-nya?'],
            'answer' => ['id' => 'Satu kontainer 20FT.'],
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.faqs.index'))
        ->assertSessionHasNoErrors();

    $faq = Faq::query()->latest('id')->first();

    // Urutan dihitung per kategori: kategori kedua terakhir di 7, jadi 8.
    expect($faq->sort_order)->toBe(8)
        ->and($faq->faq_category_id)->toBe($second->id);
});

test('pertanyaan dan jawaban wajib diisi', function () {
    $this->actingAs($this->user)
        ->post(route('admin.faqs.store'), [])
        ->assertSessionHasErrors(['question.id', 'answer.id']);

    expect(Faq::query()->count())->toBe(0);
});

test('pindah kategori menaruh FAQ di urutan terakhir kategori baru', function () {
    $from = FaqCategory::factory()->create();
    $to = FaqCategory::factory()->create();

    $faq = Faq::factory()->create(['faq_category_id' => $from->id, 'sort_order' => 0]);
    Faq::factory()->create(['faq_category_id' => $to->id, 'sort_order' => 5]);

    $this->actingAs($this->user)
        ->put(route('admin.faqs.update', $faq), [
            'faq_category_id' => $to->id,
            'question' => ['id' => 'Pertanyaan'],
            'answer' => ['id' => 'Jawaban'],
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors();

    expect($faq->refresh()->faq_category_id)->toBe($to->id)
        ->and($faq->sort_order)->toBe(6);
});

test('urutan tidak berubah bila kategori tetap sama', function () {
    $category = FaqCategory::factory()->create();
    $faq = Faq::factory()->create(['faq_category_id' => $category->id, 'sort_order' => 2]);

    $this->actingAs($this->user)
        ->put(route('admin.faqs.update', $faq), [
            'faq_category_id' => $category->id,
            'question' => ['id' => 'Pertanyaan baru'],
            'answer' => ['id' => 'Jawaban baru'],
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors();

    expect($faq->refresh()->sort_order)->toBe(2);
});

test('urutan FAQ dalam kategori tersimpan', function () {
    $category = FaqCategory::factory()->create();
    $a = Faq::factory()->create(['faq_category_id' => $category->id, 'sort_order' => 0]);
    $b = Faq::factory()->create(['faq_category_id' => $category->id, 'sort_order' => 1]);

    $this->actingAs($this->user)
        ->post(route('admin.faqs.reorder'), ['ids' => [$b->id, $a->id]])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($b->refresh()->sort_order)->toBe(0)
        ->and($a->refresh()->sort_order)->toBe(1);
});

test('FAQ dihapus secara soft delete', function () {
    $faq = Faq::factory()->create();

    $this->actingAs($this->user)
        ->delete(route('admin.faqs.destroy', $faq))
        ->assertRedirect();

    expect(Faq::query()->count())->toBe(0)
        ->and(Faq::withTrashed()->count())->toBe(1);
});

test('kategori FAQ dapat dikelola', function () {
    $this->actingAs($this->user)
        ->post(route('admin.faq-categories.store'), [
            'name' => ['id' => 'Pengiriman'],
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.faq-categories.index'))
        ->assertSessionHasNoErrors();

    $category = FaqCategory::query()->firstOrFail();

    expect($category->getTranslation('name', 'id'))->toBe('Pengiriman');

    $this->actingAs($this->user)
        ->get(route('admin.faq-categories.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/faq-categories/index')
            ->has('items', 1)
            ->where('items.0.faqs_count', 0),
        );
});

test('menghapus kategori memindahkan pertanyaannya ke kelompok lainnya', function () {
    $category = FaqCategory::factory()->create();
    $faq = Faq::factory()->create(['faq_category_id' => $category->id]);

    $this->actingAs($this->user)
        ->delete(route('admin.faq-categories.destroy', $category))
        ->assertRedirect();

    expect($faq->refresh()->faq_category_id)->toBeNull()
        ->and(Faq::query()->count())->toBe(1)
        ->and(FaqCategory::query()->count())->toBe(0);
});

test('seeder mengisi FAQ awal tanpa duplikat', function () {
    $this->seed(FaqSeeder::class);
    $this->seed(FaqSeeder::class);

    expect(FaqCategory::query()->count())->toBe(4)
        ->and(Faq::query()->count())->toBe(7);
});
