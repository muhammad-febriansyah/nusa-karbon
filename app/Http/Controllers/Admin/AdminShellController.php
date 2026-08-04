<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminNotificationService;
use App\Services\AdminSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint kecil untuk kerangka admin: pencarian global dan lonceng notifikasi.
 *
 * Keduanya membalas JSON, bukan halaman Inertia, karena dipanggil dari dropdown
 * dan dialog yang tidak boleh mengganti halaman yang sedang dibuka.
 */
final class AdminShellController extends Controller
{
    public function search(Request $request, AdminSearchService $search): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json([
            'groups' => $search->search((string) ($validated['q'] ?? '')),
        ]);
    }

    public function notifications(AdminNotificationService $notifications): JsonResponse
    {
        return response()->json($notifications->unread());
    }
}
