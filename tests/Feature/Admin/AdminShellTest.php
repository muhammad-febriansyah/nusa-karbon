<?php

declare(strict_types=1);

use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Product;
use App\Models\RfqRequest;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('tamu tidak bisa memakai pencarian dan notifikasi', function (string $route) {
    $this->get(route($route))->assertRedirect(route('login'));
})->with(['admin.search', 'admin.notifications']);

test('pencarian menemukan produk berdasarkan nama maupun kode', function () {
    Product::factory()->create([
        'code' => 'SHISHA-CUBE-26',
        'name' => ['id' => 'Briket Shisha Cube 26 mm'],
        'slug' => ['id' => 'briket-shisha-cube-26-mm'],
    ]);

    $byName = $this->actingAs($this->user)
        ->getJson(route('admin.search', ['q' => 'Shisha Cube']))
        ->assertOk()
        ->json('groups');

    expect($byName[0]['group'])->toBe('Produk')
        ->and($byName[0]['items'][0]['title'])->toBe('Briket Shisha Cube 26 mm')
        ->and($byName[0]['items'][0]['subtitle'])->toBe('SHISHA-CUBE-26');

    $byCode = $this->actingAs($this->user)
        ->getJson(route('admin.search', ['q' => 'CUBE-26']))
        ->json('groups');

    expect($byCode[0]['items'])->toHaveCount(1);
});

test('pencarian mengelompokkan hasil lintas modul', function () {
    Product::factory()->create(['name' => ['id' => 'Briket Ekspor'], 'slug' => ['id' => 'briket-ekspor']]);
    Post::factory()->create(['title' => ['id' => 'Panduan Briket Ekspor'], 'slug' => ['id' => 'panduan-briket-ekspor']]);
    RfqRequest::factory()->create(['company_name' => 'Briket Trading LLC']);

    $groups = $this->actingAs($this->user)
        ->getJson(route('admin.search', ['q' => 'Briket']))
        ->assertOk()
        ->json('groups');

    expect(collect($groups)->pluck('group')->all())
        ->toContain('Produk', 'RFQ', 'Artikel');
});

test('kata kunci terlalu pendek tidak menjalankan pencarian', function () {
    Product::factory()->create(['name' => ['id' => 'Briket'], 'slug' => ['id' => 'briket']]);

    $this->actingAs($this->user)
        ->getJson(route('admin.search', ['q' => 'b']))
        ->assertOk()
        ->assertExactJson(['groups' => []]);
});

test('kata kunci terlalu panjang ditolak validasi', function () {
    $this->actingAs($this->user)
        ->getJson(route('admin.search', ['q' => str_repeat('a', 101)]))
        ->assertStatus(422);
});

test('notifikasi memuat rfq dan pesan kontak yang belum dibaca', function () {
    RfqRequest::factory()->count(2)->create(['is_read' => false]);
    RfqRequest::factory()->read()->create();
    ContactMessage::factory()->create(['is_read' => false]);

    $response = $this->actingAs($this->user)
        ->getJson(route('admin.notifications'))
        ->assertOk()
        ->json();

    expect($response['total'])->toBe(3)
        ->and($response['items'])->toHaveCount(3)
        ->and(collect($response['items'])->pluck('type')->unique()->sort()->values()->all())
        ->toBe(['contact', 'rfq']);
});

test('notifikasi kosong saat semua sudah dibaca', function () {
    RfqRequest::factory()->read()->create();

    $this->actingAs($this->user)
        ->getJson(route('admin.notifications'))
        ->assertOk()
        ->assertJson(['total' => 0, 'items' => []]);
});
