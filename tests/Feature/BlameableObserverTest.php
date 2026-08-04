<?php

declare(strict_types=1);

use App\Models\User;
use App\Observers\BlameableObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Model sementara khusus pengujian, dibuat karena tabel domain yang punya
 * kolom audit baru ada pada Fase 2.
 */
#[ObservedBy([BlameableObserver::class])]
final class BlameableFixture extends Model
{
    protected $table = 'blameable_fixtures';

    protected $guarded = [];
}

beforeEach(function () {
    BlameableObserver::flushColumnCache();

    Schema::create('blameable_fixtures', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->foreignId('created_by')->nullable();
        $table->foreignId('updated_by')->nullable();
        $table->timestamps();
    });

    Schema::create('blameable_plain', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
});

test('created_by dan updated_by terisi otomatis saat dibuat', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $record = BlameableFixture::query()->create(['name' => 'Contoh']);

    expect($record->created_by)->toBe($user->id)
        ->and($record->updated_by)->toBe($user->id);
});

test('hanya updated_by yang berubah saat diperbarui', function () {
    $author = User::factory()->create();
    $editor = User::factory()->create();

    $this->actingAs($author);
    $record = BlameableFixture::query()->create(['name' => 'Contoh']);

    $this->actingAs($editor);
    $record->update(['name' => 'Diubah']);

    expect($record->fresh()->created_by)->toBe($author->id)
        ->and($record->fresh()->updated_by)->toBe($editor->id);
});

test('tanpa pengguna login kolom audit dibiarkan kosong', function () {
    $record = BlameableFixture::query()->create(['name' => 'Dari konsol']);

    expect($record->created_by)->toBeNull()
        ->and($record->updated_by)->toBeNull();
});

test('model tanpa kolom audit tidak menabrak error', function () {
    $this->actingAs(User::factory()->create());

    $model = new class extends Model
    {
        protected $table = 'blameable_plain';

        protected $guarded = [];
    };

    BlameableFixture::observe(BlameableObserver::class);

    expect(fn () => $model->newInstance(['name' => 'Polos'])->save())->not->toThrow(Exception::class);
});
