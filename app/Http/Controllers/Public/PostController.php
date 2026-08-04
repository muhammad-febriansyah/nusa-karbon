<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PostCardResource;
use App\Http\Resources\Public\PostDetailResource;
use App\Models\Post;
use App\Models\PostCategory;
use App\Repositories\Contracts\PostCategoryRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Blog publik.
 *
 * Daftar dan artikel dipegang satu controller karena keduanya membaca sumber
 * yang sama dan selalu berubah bersamaan.
 */
final class PostController extends Controller
{
    /** Jumlah artikel lain di bawah satu tulisan. */
    private const RELATED_LIMIT = 3;

    /**
     * Artikel per halaman arsip.
     *
     * Tujuh: satu artikel utama yang ditampilkan besar, ditambah enam di kisi
     * tiga kolom. Dua baris genap, jadi tidak pernah ada kartu tunggal yang
     * menggantung sendirian di baris terakhir.
     */
    private const PER_PAGE = 7;

    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly PostCategoryRepositoryInterface $categories,
    ) {}

    public function index(Request $request): Response
    {
        $published = $this->posts->publishedForPublic();
        $categories = $this->publicCategories($published);
        $slug = $request->string('kategori')->toString();

        /*
         * Kategori yang tidak dikenal diperlakukan sebagai "tanpa saringan".
         * Tautan lama atau salah ketik lebih baik berakhir di seluruh arsip
         * daripada di daftar kosong yang terbaca seperti blog yang mati.
         */
        $active = $categories->firstWhere(
            fn (PostCategory $category): bool => $category->getTranslation('slug', app()->getLocale()) === $slug,
        );

        $page = $this->posts->publishedPaginated($active?->id, self::PER_PAGE);

        // Dihitung dari koleksi yang sudah dimuat di atas, bukan lewat kueri
        // hitung per kategori — jumlahnya dipakai di label pilihan saringan.
        $totalPerCategory = $published->countBy('post_category_id');

        return Inertia::render('public/blog/index', [
            'posts' => [
                // `resolve()` mempertahankan kunci koleksinya; tanpa penataan
                // ulang, kunci yang meloncat membuat `json_encode` mengirimnya
                // sebagai objek dan `posts.data.map()` di React gagal.
                'data' => array_values(
                    PostCardResource::collection($page->items())->resolve(),
                ),
                'meta' => [
                    'current_page' => $page->currentPage(),
                    'last_page' => $page->lastPage(),
                    'per_page' => $page->perPage(),
                    'from' => $page->firstItem(),
                    'to' => $page->lastItem(),
                    'total' => $page->total(),
                ],
            ],
            'categories' => $categories
                ->map(fn (PostCategory $category): array => [
                    'id' => $category->id,
                    'name' => $category->getTranslation('name', app()->getLocale()),
                    'slug' => $category->getTranslation('slug', app()->getLocale()),
                    'total' => $totalPerCategory->get($category->id, 0),
                ])
                ->values()
                ->all(),
            'activeCategory' => $active === null
                ? null
                : $active->getTranslation('slug', app()->getLocale()),
            'totalPublished' => $published->count(),
        ]);
    }

    public function show(string $slug): Response
    {
        $post = $this->posts->findPublishedBySlug($slug, app()->getLocale());

        if ($post === null) {
            throw new NotFoundHttpException;
        }

        $post->load(['category:id,name,slug', 'author:id,name', 'tags']);

        return Inertia::render('public/blog/show', [
            'post' => PostDetailResource::make($post)->resolve(),
            'related' => PostCardResource::collection(
                $this->related($post),
            )->resolve(),
        ]);
    }

    /**
     * Artikel lain dari kategori yang sama.
     *
     * @return Collection<int, Post>
     */
    private function related(Post $post): Collection
    {
        return $this->posts
            ->publishedForPublic($post->post_category_id)
            ->reject(fn (Post $item): bool => $item->is($post))
            ->take(self::RELATED_LIMIT)
            ->values();
    }

    /**
     * Kategori yang benar-benar punya artikel terbit.
     *
     * Basis data memuat lebih banyak kategori daripada artikel. Menampilkan
     * semuanya sebagai saringan berarti sebagian besar tombol menghasilkan
     * daftar kosong.
     *
     * @param  Collection<int, Post>  $published
     * @return Collection<int, PostCategory>
     */
    private function publicCategories(Collection $published): Collection
    {
        $used = $published
            ->pluck('post_category_id')
            ->filter()
            ->unique();

        return $this->categories->ordered()
            ->filter(fn (PostCategory $category): bool => $used->contains($category->id))
            ->values();
    }
}
