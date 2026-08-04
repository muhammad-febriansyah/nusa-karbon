<?php

declare(strict_types=1);

use App\Models\ContainerType;
use App\Models\PackagingOption;
use App\Models\Product;
use App\Models\ProductDocument;
use App\Models\ProductSpecification;
use App\Models\RfqRequest;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('tamu tidak bisa membuka detail produk', function () {
    $product = Product::factory()->create();

    $this->get(route('admin.products.show', $product))
        ->assertRedirect(route('login'));
});

test('detail produk menampilkan spesifikasi, dokumen, dan kemasan', function () {
    $product = Product::factory()->calculable()->create();

    ProductSpecification::factory()->count(2)->create(['product_id' => $product->id]);
    ProductDocument::factory()->create(['product_id' => $product->id]);

    $packaging = PackagingOption::factory()->create();
    $product->packagingOptions()->attach($packaging);

    $this->actingAs($this->user)
        ->get(route('admin.products.show', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/products/show')
            ->where('product.id', $product->id)
            ->where('product.is_calculable', true)
            ->has('product.specifications', 2)
            ->has('product.documents', 1)
            ->has('packagingOptions', 1)
            ->where('packagingOptions.0.id', $packaging->id),
        );
});

test('estimasi muatan dihitung untuk tiap kontainer aktif', function () {
    $product = Product::factory()->calculable()->create();

    ContainerType::factory()->create(['code' => '20FT', 'sort_order' => 1]);
    ContainerType::factory()->highCube()->create(['code' => '40HC', 'sort_order' => 2]);
    ContainerType::factory()->inactive()->create(['code' => '45HC']);

    $this->actingAs($this->user)
        ->get(route('admin.products.show', $product))
        ->assertInertia(fn (Assert $page) => $page
            ->has('containerLoads', 2)
            ->where('containerLoads.0.code', '20FT')
            // 18.000 kg / 20 kg = 900 karton; 28 CBM / 0,043 = 651 karton.
            ->where('containerLoads.0.cartons', 651)
            ->where('containerLoads.0.limited_by', 'volume'),
        );
});

test('produk tanpa data master carton tidak menghasilkan estimasi muatan', function () {
    $product = Product::factory()->create();

    ContainerType::factory()->create();

    $this->actingAs($this->user)
        ->get(route('admin.products.show', $product))
        ->assertInertia(fn (Assert $page) => $page
            ->where('product.is_calculable', false)
            ->has('containerLoads', 0),
        );
});

test('permintaan terkait dibatasi lima terbaru dengan total penuh', function () {
    $product = Product::factory()->create();

    RfqRequest::factory()->count(7)->create(['product_id' => $product->id]);
    RfqRequest::factory()->create();

    $this->actingAs($this->user)
        ->get(route('admin.products.show', $product))
        ->assertInertia(fn (Assert $page) => $page
            ->has('rfqs', 5)
            ->where('rfqTotal', 7),
        );
});
