<?php

declare(strict_types=1);

use App\Enums\RfqStatus;
use App\Models\Certificate;
use App\Models\Post;
use App\Models\Product;
use App\Models\RfqRequest;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
    Cache::flush();
});

test('dashboard menampilkan statistik akurat', function () {
    RfqRequest::factory()->count(3)->create();
    RfqRequest::factory()->read()->create();
    Product::factory()->calculable()->count(2)->create();
    Product::factory()->inactive()->create();
    Post::factory()->published()->count(2)->create();
    Post::factory()->create();

    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard/index')
            ->where('stats.0.value', 3)
            ->where('stats.1.value', 4)
            ->where('stats.2.value', 2)
            ->where('stats.3.value', 2)
            ->has('monthlyRfqs', 12)
            ->has('recentRfqs', 4),
        );
});

test('panel perhatian menyebut produk tanpa master carton dan sertifikat kedaluwarsa', function () {
    Product::factory()->create();
    Certificate::factory()->expiringSoon(30)->create();

    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('attention.0.key', 'products_incomplete')
            ->where('attention.0.total', 1)
            ->where('attention.1.key', 'certificates_expiring')
            ->where('attention.1.total', 1),
        );
});

test('negara terbanyak diurutkan menurun', function () {
    RfqRequest::factory()->count(3)->create(['country_code' => 'AE']);
    RfqRequest::factory()->create(['country_code' => 'DE']);

    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('topCountries.0.country_code', 'AE')
            ->where('topCountries.0.total', 3)
            ->where('topCountries.1.country_code', 'DE'),
        );
});

test('ringkasan dashboard dicache dan bisa dikosongkan', function () {
    RfqRequest::factory()->create();

    $dashboard = app(DashboardService::class);

    expect($dashboard->summary()['stats'][0]['value'])->toBe(1);

    RfqRequest::factory()->create();

    // Masih memakai cache lima menit.
    expect($dashboard->summary()['stats'][0]['value'])->toBe(1);

    $dashboard->flush();

    expect($dashboard->summary()['stats'][0]['value'])->toBe(2);
});

test('corong status menghitung jumlah dan porsinya per tahap', function () {
    RfqRequest::factory()->count(3)->create();
    RfqRequest::factory()->status(RfqStatus::Won)->create();

    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertInertia(function (Assert $page): void {
            $funnel = collect($page->toArray()['props']['funnel']);

            expect($funnel)->toHaveCount(7)
                ->and($funnel->firstWhere('status', 'new')['total'])->toBe(3)
                // JSON menulis 75.0 sebagai 75, jadi dibandingkan tanpa tipe.
                ->and($funnel->firstWhere('status', 'new')['share'])->toEqual(75)
                ->and($funnel->firstWhere('status', 'won')['total'])->toBe(1);
        });
});

test('tingkat konversi mengabaikan spam', function () {
    RfqRequest::factory()->status(RfqStatus::Won)->create();
    RfqRequest::factory()->status(RfqStatus::Lost)->create();
    RfqRequest::factory()->spam()->count(8)->create();

    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            // Dua permintaan diproses, satu menang — spam tidak menurunkan angkanya.
            ->where('conversion.processed', 2)
            ->where('conversion.won', 1)
            ->where('conversion.rate', 50),
        );
});

test('produk paling diminati diurutkan menurun', function () {
    $populer = Product::factory()->create(['name' => ['id' => 'Briket Cube 26']]);
    $lain = Product::factory()->create(['name' => ['id' => 'Briket Hexagonal']]);

    RfqRequest::factory()->count(2)->create(['product_id' => $populer->id]);
    RfqRequest::factory()->create(['product_id' => $lain->id]);
    RfqRequest::factory()->create(['product_id' => null]);

    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('topProducts', 2)
            ->where('topProducts.0.name', 'Briket Cube 26')
            ->where('topProducts.0.total', 2),
        );
});

test('ringkasan isi situs dan umpan aktivitas ikut dikirim', function () {
    $this->actingAs($this->user);

    Certificate::factory()->count(2)->create();

    $this->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('content', 4)
            ->where('content.0.value', 2)
            ->has('recentActivities'),
        );
});

test('stat RFQ bulan ini membawa selisih terhadap bulan lalu', function () {
    RfqRequest::factory()->count(3)->create();
    RfqRequest::factory()->count(2)->create([
        'created_at' => now()->subMonthNoOverflow()->startOfMonth()->addDay(),
    ]);

    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.1.value', 3)
            // Naik dari 2 ke 3 = +50%.
            ->where('stats.1.delta', 50),
        );
});
