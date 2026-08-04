<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ContactMessageResource;
use App\Models\ContactMessage;
use App\Repositories\Contracts\ContactMessageRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ContactMessageController extends Controller
{
    public function __construct(
        private readonly ContactMessageRepositoryInterface $messages,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search', 'unread', 'is_spam', 'sort_by', 'sort_dir', 'per_page',
        ]);

        return Inertia::render('admin/contact-messages/index', [
            'messages' => ContactMessageResource::collection(
                $this->messages->paginate($filters, $request->integer('per_page', 15)),
            ),
            'unreadCount' => $this->messages->countUnread(),
            'filters' => $filters,
        ]);
    }

    /**
     * Membuka pesan sekaligus menandainya sudah dibaca.
     */
    public function show(ContactMessage $contactMessage): Response
    {
        if (! $contactMessage->is_read) {
            $this->messages->update($contactMessage, ['is_read' => true]);
        }

        return Inertia::render('admin/contact-messages/show', [
            'message' => (new ContactMessageResource($contactMessage))->resolve(),
        ]);
    }

    public function update(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validate([
            'is_read' => ['boolean'],
            'is_spam' => ['boolean'],
        ]);

        $this->messages->update($contactMessage, $validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pesan berhasil diperbarui.',
        ]);

        return back();
    }

    /**
     * Pesan spam dihapus permanen; tidak ada nilainya menyimpan sampah.
     */
    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $this->messages->delete($contactMessage);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pesan berhasil dihapus.',
        ]);

        return to_route('admin.contact-messages.index');
    }
}
