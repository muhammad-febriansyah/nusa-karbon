<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Certificate\CreateCertificateAction;
use App\Actions\Certificate\DeleteCertificateAction;
use App\Actions\Certificate\UpdateCertificateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Certificate\StoreCertificateRequest;
use App\Http\Requests\Admin\Certificate\UpdateCertificateRequest;
use App\Http\Resources\Admin\CertificateResource;
use App\Models\Certificate;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CertificateController extends Controller
{
    public function __construct(
        private readonly CertificateRepositoryInterface $certificates,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search', 'is_active', 'expiry', 'sort_by', 'sort_dir', 'per_page',
        ]);

        return Inertia::render('admin/certificates/index', [
            'certificates' => CertificateResource::collection(
                $this->certificates->paginate($filters, $request->integer('per_page', 15)),
            ),
            'expiringCount' => $this->certificates->expiringWithin()->count(),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/certificates/create');
    }

    public function store(
        StoreCertificateRequest $request,
        CreateCertificateAction $action,
    ): RedirectResponse {
        $action->handle($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Sertifikat berhasil ditambahkan.',
        ]);

        return to_route('admin.certificates.index');
    }

    public function edit(Certificate $certificate): Response
    {
        return Inertia::render('admin/certificates/edit', [
            // `resolve()` supaya tidak terbungkus kunci `data`; halaman form
            // mengharapkan objeknya langsung.
            'certificate' => (new CertificateResource($certificate))->resolve(),
        ]);
    }

    public function update(
        UpdateCertificateRequest $request,
        Certificate $certificate,
        UpdateCertificateAction $action,
    ): RedirectResponse {
        $action->handle($certificate, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan sertifikat berhasil disimpan.',
        ]);

        return to_route('admin.certificates.index');
    }

    public function destroy(
        Certificate $certificate,
        DeleteCertificateAction $action,
    ): RedirectResponse {
        $action->handle($certificate);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Sertifikat berhasil dihapus.',
        ]);

        return back();
    }
}
