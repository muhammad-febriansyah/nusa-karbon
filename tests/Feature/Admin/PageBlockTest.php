<?php

declare(strict_types=1);

use App\Enums\PageBlockType;
use App\Models\PageBlock;
use App\Models\User;
use Database\Seeders\PageBlockSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('tamu tidak bisa mengakses blok halaman', function () {
    $this->get(route('admin.page-blocks.index'))
        ->assertRedirect(route('login'));
});

test('halaman index menampilkan blok berurutan per halaman', function () {
    PageBlock::factory()->create(['page_key' => 'about', 'sort_order' => 1]);
    PageBlock::factory()->create(['page_key' => 'about', 'sort_order' => 0]);
    PageBlock::factory()->create(['page_key' => 'home', 'sort_order' => 0]);

    $this->actingAs($this->user)
        ->get(route('admin.page-blocks.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/page-blocks/index')
            ->has('blocks', 3)
            ->where('blocks.0.page_key', 'about')
            ->where('blocks.0.sort_order', 0),
        );
});

test('isi blok dapat diubah', function () {
    $block = PageBlock::factory()->create([
        'page_key' => 'about',
        'block_key' => 'company_profile',
    ]);

    $this->actingAs($this->user)
        ->put(route('admin.page-blocks.update', $block), [
            'title' => ['id' => 'Profil Baru'],
            'content' => ['id' => '<p>Isi baru.</p>'],
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.page-blocks.index'))
        ->assertSessionHasNoErrors();

    $block->refresh();

    expect($block->getTranslation('title', 'id'))->toBe('Profil Baru')
        ->and($block->getTranslation('content', 'id'))->toBe('<p>Isi baru.</p>');
});

test('kunci blok dan tipe tidak dapat diubah dari UI', function () {
    $block = PageBlock::factory()->create([
        'page_key' => 'about',
        'block_key' => 'company_profile',
        'type' => PageBlockType::RichText,
    ]);

    $this->actingAs($this->user)
        ->put(route('admin.page-blocks.update', $block), [
            'page_key' => 'home',
            'block_key' => 'hero',
            'type' => PageBlockType::Stat->value,
            'title' => ['id' => 'Judul'],
        ])
        ->assertSessionHasNoErrors();

    $block->refresh();

    expect($block->page_key)->toBe('about')
        ->and($block->block_key)->toBe('company_profile')
        ->and($block->type)->toBe(PageBlockType::RichText);
});

test('blok tidak bisa dibuat atau dihapus lewat route', function () {
    $block = PageBlock::factory()->create();

    // Route store dan destroy memang tidak didaftarkan, sehingga Laravel
    // menolaknya dengan 405 Method Not Allowed.
    $this->actingAs($this->user)
        ->post('/admin/page-blocks', ['page_key' => 'home', 'block_key' => 'baru'])
        ->assertMethodNotAllowed();

    $this->actingAs($this->user)
        ->delete("/admin/page-blocks/{$block->id}")
        ->assertMethodNotAllowed();

    expect(PageBlock::query()->count())->toBe(1);
});

test('butir daftar tersimpan pada blok bertipe list', function () {
    $block = PageBlock::factory()->ofType(PageBlockType::ListItems)->create();

    $this->actingAs($this->user)
        ->put(route('admin.page-blocks.update', $block), [
            'title' => ['id' => 'Termin Pembayaran'],
            'extra' => ['items' => ['30% uang muka', '70% sebelum dokumen dikirim']],
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors();

    expect($block->refresh()->extra['items'])->toBe([
        '30% uang muka',
        '70% sebelum dokumen dikirim',
    ]);
});

test('seeder mengisi seluruh kunci blok tanpa duplikat', function () {
    $this->seed(PageBlockSeeder::class);
    $this->seed(PageBlockSeeder::class);

    expect(PageBlock::query()->count())->toBe(12)
        ->and(PageBlock::query()->where('page_key', 'about')->count())->toBe(4)
        ->and(PageBlock::query()
            ->where('page_key', 'about')
            ->where('block_key', 'company_profile')
            ->exists())->toBeTrue();
});
