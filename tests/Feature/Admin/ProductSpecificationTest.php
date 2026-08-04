<?php

declare(strict_types=1);

use App\Actions\Product\SyncProductSpecificationsAction;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\User;
use App\Support\SpecificationTemplate;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->sync = app(SyncProductSpecificationsAction::class);
});

test('spesifikasi ikut tersimpan saat produk dibuat', function () {
    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'SPEC-01',
            'name' => ['id' => 'Produk Spesifikasi'],
            'slug' => ['id' => 'produk-spesifikasi'],
            'specifications' => [
                [
                    'parameter' => ['id' => 'Fixed Carbon'],
                    'value' => '80',
                    'unit' => '% min',
                    'is_highlighted' => true,
                ],
                [
                    'parameter' => ['id' => 'Ash Content'],
                    'value' => '2,5',
                    'unit' => '% max',
                    'is_highlighted' => false,
                ],
            ],
        ])
        ->assertSessionHasNoErrors();

    $product = Product::query()->with('specifications')->first();

    expect($product->specifications)->toHaveCount(2)
        ->and($product->specifications[0]->getTranslation('parameter', 'id'))->toBe('Fixed Carbon')
        ->and($product->specifications[0]->sort_order)->toBe(0)
        ->and($product->specifications[0]->is_highlighted)->toBeTrue()
        ->and($product->specifications[1]->sort_order)->toBe(1);
});

test('nama parameter wajib diisi', function () {
    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'SPEC-02',
            'name' => ['id' => 'Produk'],
            'slug' => ['id' => 'produk'],
            'specifications' => [
                ['parameter' => ['id' => ''], 'value' => '80'],
            ],
        ])
        ->assertSessionHasErrors([
            'specifications.0.parameter.id' => 'Nama parameter spesifikasi wajib diisi.',
        ]);
});

test('nilai spesifikasi wajib diisi', function () {
    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'SPEC-03',
            'name' => ['id' => 'Produk'],
            'slug' => ['id' => 'produk'],
            'specifications' => [
                ['parameter' => ['id' => 'Fixed Carbon'], 'value' => ''],
            ],
        ])
        ->assertSessionHasErrors([
            'specifications.0.value' => 'Nilai spesifikasi wajib diisi.',
        ]);
});

/*
 * Inti dari Action ini: menyimpan ulang form tidak boleh menggandakan baris.
 */
test('sinkronisasi berulang tidak menduplikasi baris', function () {
    $product = Product::factory()->create();

    $payload = [
        [
            'parameter' => ['id' => 'Fixed Carbon'],
            'value' => '80',
            'unit' => '% min',
        ],
    ];

    $this->sync->handle($product, $payload);
    $first = $product->specifications()->first();

    // Kiriman kedua membawa id yang sama, seperti saat form disimpan ulang.
    $this->sync->handle($product, [
        ['id' => $first->id] + $payload[0],
    ]);

    expect($product->specifications()->count())->toBe(1)
        ->and($product->specifications()->first()->id)->toBe($first->id);
});

test('baris yang dihapus dari form ikut terhapus', function () {
    $product = Product::factory()->create();

    $this->sync->handle($product, [
        ['parameter' => ['id' => 'Fixed Carbon'], 'value' => '80'],
        ['parameter' => ['id' => 'Ash Content'], 'value' => '2'],
        ['parameter' => ['id' => 'Moisture'], 'value' => '5'],
    ]);

    $kept = $product->specifications()->first();

    $this->sync->handle($product, [
        ['id' => $kept->id, 'parameter' => ['id' => 'Fixed Carbon'], 'value' => '82'],
    ]);

    expect($product->specifications()->count())->toBe(1)
        ->and($product->specifications()->first()->value)->toBe('82');
});

test('urutan baris mengikuti urutan kiriman', function () {
    $product = Product::factory()->create();

    $this->sync->handle($product, [
        ['parameter' => ['id' => 'Pertama'], 'value' => '1'],
        ['parameter' => ['id' => 'Kedua'], 'value' => '2'],
    ]);

    $ids = $product->specifications()->pluck('id')->all();

    // Ditukar posisinya, seperti hasil drag-and-drop.
    $this->sync->handle($product, [
        ['id' => $ids[1], 'parameter' => ['id' => 'Kedua'], 'value' => '2'],
        ['id' => $ids[0], 'parameter' => ['id' => 'Pertama'], 'value' => '1'],
    ]);

    expect($product->specifications()->pluck('sort_order', 'id')->all())
        ->toBe([$ids[1] => 0, $ids[0] => 1]);
});

test('mengirim larik kosong menghapus seluruh spesifikasi', function () {
    $product = Product::factory()->create();
    ProductSpecification::factory()->count(3)->create(['product_id' => $product->id]);

    $this->sync->handle($product, []);

    expect($product->specifications()->count())->toBe(0);
});

test('id milik produk lain diabaikan dan dibuat sebagai baris baru', function () {
    $product = Product::factory()->create();
    $other = ProductSpecification::factory()->create();

    $this->sync->handle($product, [
        ['id' => $other->id, 'parameter' => ['id' => 'Fixed Carbon'], 'value' => '80'],
    ]);

    expect($product->specifications()->count())->toBe(1)
        ->and($product->specifications()->first()->id)->not->toBe($other->id)
        ->and(ProductSpecification::query()->whereKey($other->id)->exists())->toBeTrue();
});

test('menghapus produk ikut menghapus spesifikasinya', function () {
    $product = Product::factory()->create();
    ProductSpecification::factory()->count(2)->create(['product_id' => $product->id]);

    $product->forceDelete();

    expect(ProductSpecification::query()->where('product_id', $product->id)->count())
        ->toBe(0);
});

test('memperbarui produk tanpa mengirim spesifikasi tidak menghapusnya', function () {
    $product = Product::factory()->create();
    ProductSpecification::factory()->count(2)->create(['product_id' => $product->id]);

    $this->actingAs($this->user)
        ->put(route('admin.products.update', $product), [
            'code' => $product->code,
            'name' => ['id' => 'Nama Baru'],
            'slug' => ['id' => 'nama-baru'],
        ])
        ->assertSessionHasNoErrors();

    expect($product->specifications()->count())->toBe(2);
});

test('template memuat sembilan parameter standar', function () {
    $template = SpecificationTemplate::standard();

    expect($template)->toHaveCount(9)
        ->and(array_column($template, 'parameter'))->toContain(
            'Fixed Carbon',
            'Ash Content',
            'Calorific Value',
        );
});

test('template dikirim ke halaman form', function () {
    $this->actingAs($this->user)
        ->get(route('admin.products.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/products/create')
            ->has('specificationTemplate', 9)
            ->where('specificationTemplate.0.parameter', 'Fixed Carbon'),
        );
});

test('spesifikasi ikut dikirim ke halaman ubah', function () {
    $product = Product::factory()->create();
    ProductSpecification::factory()->create([
        'product_id' => $product->id,
        'parameter' => ['id' => 'Fixed Carbon'],
        'value' => '80',
    ]);

    $this->actingAs($this->user)
        ->get(route('admin.products.edit', $product))
        ->assertInertia(fn (Assert $page) => $page
            ->has('product.specifications', 1)
            ->where('product.specifications.0.value', '80'),
        );
});
