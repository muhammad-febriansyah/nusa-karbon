<?php

declare(strict_types=1);

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\PostSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function postPayload(array $overrides = []): array
{
    return array_merge([
        'title' => ['id' => 'Memahami Incoterms untuk Briket'],
        'slug' => ['id' => 'memahami-incoterms-untuk-briket'],
        'excerpt' => ['id' => 'Tiga term yang paling sering dipakai buyer.'],
        'content' => ['id' => '<p>Isi artikel.</p>'],
        'status' => PostStatus::Draft->value,
        'tags' => [],
    ], $overrides);
}

test('tamu tidak bisa mengakses artikel', function () {
    $this->get(route('admin.posts.index'))
        ->assertRedirect(route('login'));
});

test('halaman index memuat artikel beserta filter', function () {
    Post::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->get(route('admin.posts.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/posts/index')
            ->has('posts.data', 3)
            ->has('categories')
            ->has('statuses', 4)
            ->has('filters'),
        );
});

test('artikel baru tersimpan beserta penulis dan waktu baca', function () {
    $this->actingAs($this->user)
        ->post(route('admin.posts.store'), postPayload([
            'content' => ['id' => '<p>'.str_repeat('kata ', 450).'</p>'],
        ]))
        ->assertRedirect(route('admin.posts.index'))
        ->assertSessionHasNoErrors();

    $post = Post::query()->firstOrFail();

    // 450 kata dibagi 200 kata per menit, dibulatkan ke atas.
    expect($post->reading_minutes)->toBe(3)
        ->and($post->user_id)->toBe($this->user->id)
        ->and($post->status)->toBe(PostStatus::Draft);
});

test('HTML berbahaya dibersihkan sebelum disimpan', function () {
    $this->actingAs($this->user)
        ->post(route('admin.posts.store'), postPayload([
            'content' => ['id' => '<p>Aman</p><script>alert(1)</script><img src=x onerror="alert(2)">'],
        ]))
        ->assertSessionHasNoErrors();

    $content = Post::query()->firstOrFail()->getTranslation('content', 'id');

    expect($content)->toContain('Aman')
        ->and($content)->not->toContain('<script')
        ->and($content)->not->toContain('onerror');
});

test('slug artikel wajib unik', function () {
    Post::factory()->create(['slug' => ['id' => 'memahami-incoterms-untuk-briket']]);

    $this->actingAs($this->user)
        ->post(route('admin.posts.store'), postPayload())
        ->assertSessionHasErrors('slug.id');
});

test('slug diisi otomatis dari judul bila dikosongkan', function () {
    $this->actingAs($this->user)
        ->post(route('admin.posts.store'), postPayload(['slug' => ['id' => '']]))
        ->assertSessionHasNoErrors();

    expect(Post::query()->firstOrFail()->getTranslation('slug', 'id'))
        ->toBe('memahami-incoterms-untuk-briket');
});

test('artikel terjadwal wajib punya tanggal tayang', function () {
    $this->actingAs($this->user)
        ->post(route('admin.posts.store'), postPayload([
            'status' => PostStatus::Scheduled->value,
        ]))
        ->assertSessionHasErrors('published_at');

    expect(Post::query()->count())->toBe(0);
});

test('artikel terbit tanpa tanggal tayang dianggap tayang sekarang', function () {
    $this->actingAs($this->user)
        ->post(route('admin.posts.store'), postPayload([
            'status' => PostStatus::Published->value,
        ]))
        ->assertSessionHasNoErrors();

    expect(Post::query()->firstOrFail()->published_at)->not->toBeNull();
});

test('tag dibuat dan disinkronkan tanpa duplikat', function () {
    $this->actingAs($this->user)
        ->post(route('admin.posts.store'), postPayload([
            'tags' => ['Ekspor', 'Incoterms'],
        ]))
        ->assertSessionHasNoErrors();

    $post = Post::query()->firstOrFail();

    expect($post->tags()->count())->toBe(2)
        ->and(Tag::query()->count())->toBe(2);

    $this->actingAs($this->user)
        ->put(route('admin.posts.update', $post), postPayload([
            'tags' => ['Ekspor', 'Logistik'],
        ]))
        ->assertSessionHasNoErrors();

    expect($post->refresh()->tags()->count())->toBe(2)
        ->and(Tag::query()->count())->toBe(3);
});

test('artikel dapat diterbitkan lewat aksi cepat', function () {
    $post = Post::factory()->create(['status' => PostStatus::Draft]);

    $this->actingAs($this->user)
        ->post(route('admin.posts.publish', $post))
        ->assertRedirect();

    $post->refresh();

    expect($post->status)->toBe(PostStatus::Published)
        ->and($post->published_at)->not->toBeNull();
});

test('command menerbitkan artikel terjadwal yang waktunya sudah lewat', function () {
    $due = Post::factory()->dueForPublishing()->create();
    $future = Post::factory()->scheduled()->create();

    $this->artisan('posts:publish-scheduled')
        ->expectsOutputToContain('1 artikel terjadwal diterbitkan.')
        ->assertSuccessful();

    expect($due->refresh()->status)->toBe(PostStatus::Published)
        ->and($future->refresh()->status)->toBe(PostStatus::Scheduled);
});

test('scope published hanya memuat artikel yang sudah tayang', function () {
    Post::factory()->published()->create();
    Post::factory()->scheduled()->create();
    Post::factory()->create();

    expect(Post::query()->published()->count())->toBe(1);
});

test('halaman pratinjau menampilkan artikel beserta kategorinya', function () {
    $post = Post::factory()->create();

    $this->actingAs($this->user)
        ->get(route('admin.posts.preview', $post))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/posts/preview')
            ->has('post.content')
            ->has('post.tags'),
        );
});

test('artikel dihapus secara soft delete', function () {
    $post = Post::factory()->create();

    $this->actingAs($this->user)
        ->delete(route('admin.posts.destroy', $post))
        ->assertRedirect();

    expect(Post::query()->count())->toBe(0)
        ->and(Post::withTrashed()->count())->toBe(1);
});

test('filter status dan kategori menyaring daftar', function () {
    $category = PostCategory::factory()->create();

    Post::factory()->published()->create(['post_category_id' => $category->id]);
    Post::factory()->create(['post_category_id' => $category->id]);
    Post::factory()->published()->create();

    $this->actingAs($this->user)
        ->get(route('admin.posts.index', ['status' => 'published']))
        ->assertInertia(fn (Assert $page) => $page->has('posts.data', 2));

    $this->actingAs($this->user)
        ->get(route('admin.posts.index', [
            'status' => 'published',
            'post_category_id' => $category->id,
        ]))
        ->assertInertia(fn (Assert $page) => $page->has('posts.data', 1));
});

test('kategori artikel dapat dikelola', function () {
    $this->actingAs($this->user)
        ->post(route('admin.post-categories.store'), [
            'name' => ['id' => 'Panduan Ekspor'],
            'slug' => ['id' => ''],
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.post-categories.index'))
        ->assertSessionHasNoErrors();

    $category = PostCategory::query()->firstOrFail();

    expect($category->getTranslation('slug', 'id'))->toBe('panduan-ekspor');
});

test('menghapus kategori artikel tidak menghapus artikelnya', function () {
    $category = PostCategory::factory()->create();
    $post = Post::factory()->create(['post_category_id' => $category->id]);

    $this->actingAs($this->user)
        ->delete(route('admin.post-categories.destroy', $category))
        ->assertRedirect();

    expect(Post::query()->count())->toBe(1)
        ->and($post->refresh()->post_category_id)->toBe($category->id);
});

test('seeder mengisi artikel awal tanpa duplikat', function () {
    $this->seed(PostSeeder::class);
    $this->seed(PostSeeder::class);

    expect(PostCategory::query()->count())->toBe(3)
        ->and(Post::query()->count())->toBe(3)
        ->and(Post::query()->published()->count())->toBe(2);
});
