<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RfqNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Catatan tindak lanjut pada satu RFQ.
 *
 * @property int $id
 * @property int $rfq_request_id
 * @property int|null $user_id
 * @property string $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read RfqRequest $rfqRequest
 * @property-read User|null $user
 */
final class RfqNote extends Model
{
    /** @use HasFactory<RfqNoteFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['rfq_request_id', 'user_id', 'note'];

    /**
     * @return BelongsTo<RfqRequest, $this>
     */
    public function rfqRequest(): BelongsTo
    {
        return $this->belongsTo(RfqRequest::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
