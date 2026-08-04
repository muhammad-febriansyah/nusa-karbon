<?php

declare(strict_types=1);

namespace App\Actions\Rfq;

use App\Models\RfqNote;
use App\Models\RfqRequest;
use Illuminate\Support\Facades\Auth;

final readonly class AddRfqNoteAction
{
    public function handle(RfqRequest $rfq, string $note): RfqNote
    {
        return $rfq->notes()->create([
            'user_id' => Auth::id(),
            'note' => $note,
        ]);
    }
}
