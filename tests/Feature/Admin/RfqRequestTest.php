<?php

declare(strict_types=1);

use App\Enums\RfqStatus;
use App\Models\ContactMessage;
use App\Models\RfqNote;
use App\Models\RfqRequest;
use App\Models\User;
use App\Repositories\Contracts\RfqRequestRepositoryInterface;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('tamu tidak bisa mengakses RFQ inbox', function () {
    $this->get(route('admin.rfqs.index'))
        ->assertRedirect(route('login'));
});

test('halaman index memuat RFQ beserta jumlah belum dibaca', function () {
    RfqRequest::factory()->count(2)->create();
    RfqRequest::factory()->read()->create();

    $this->actingAs($this->user)
        ->get(route('admin.rfqs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/rfqs/index')
            ->has('rfqs.data', 3)
            ->where('unreadCount', 2)
            ->has('statuses', 7),
        );
});

test('nomor referensi berurutan per hari', function () {
    $rfqs = app(RfqRequestRepositoryInterface::class);
    $today = now()->format('Ymd');

    expect($rfqs->nextReferenceNo())->toBe("RFQ-{$today}-0001");

    RfqRequest::factory()->create(['reference_no' => "RFQ-{$today}-0001"]);
    RfqRequest::factory()->create(['reference_no' => "RFQ-{$today}-0002"]);

    expect($rfqs->nextReferenceNo())->toBe("RFQ-{$today}-0003");
});

test('membuka detail menandai RFQ sudah dibaca', function () {
    $rfq = RfqRequest::factory()->create();

    $this->actingAs($this->user)
        ->get(route('admin.rfqs.show', $rfq))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/rfqs/show')
            ->has('rfq.notes'),
        );

    expect($rfq->refresh()->is_read)->toBeTrue();
});

test('status RFQ dapat diubah dan sekaligus menandai dibaca', function () {
    $rfq = RfqRequest::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.rfqs.status', $rfq), ['status' => RfqStatus::Quoted->value])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $rfq->refresh();

    expect($rfq->status)->toBe(RfqStatus::Quoted)
        ->and($rfq->is_read)->toBeTrue();
});

test('status tidak dikenal ditolak', function () {
    $rfq = RfqRequest::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.rfqs.status', $rfq), ['status' => 'entah'])
        ->assertSessionHasErrors('status');
});

test('catatan tindak lanjut tersimpan beserta penulisnya', function () {
    $rfq = RfqRequest::factory()->create();

    $this->actingAs($this->user)
        ->post(route('admin.rfqs.notes.store', $rfq), [
            'note' => 'Sudah dikirim penawaran FOB Surabaya.',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $note = RfqNote::query()->firstOrFail();

    expect($note->note)->toBe('Sudah dikirim penawaran FOB Surabaya.')
        ->and($note->user_id)->toBe($this->user->id)
        ->and($note->rfq_request_id)->toBe($rfq->id);
});

test('catatan kosong ditolak', function () {
    $rfq = RfqRequest::factory()->create();

    $this->actingAs($this->user)
        ->post(route('admin.rfqs.notes.store', $rfq), ['note' => ''])
        ->assertSessionHasErrors('note');
});

test('RFQ spam tetap tersimpan tapi keluar dari hitungan belum dibaca', function () {
    $rfq = RfqRequest::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.rfqs.spam', $rfq))
        ->assertRedirect();

    $rfq->refresh();

    expect($rfq->status)->toBe(RfqStatus::Spam)
        ->and(RfqRequest::query()->count())->toBe(1)
        ->and(app(RfqRequestRepositoryInterface::class)->countUnread())->toBe(0);
});

test('RFQ dapat ditandai belum dibaca kembali', function () {
    $rfq = RfqRequest::factory()->read()->create();

    $this->actingAs($this->user)
        ->put(route('admin.rfqs.unread', $rfq))
        ->assertRedirect();

    expect($rfq->refresh()->is_read)->toBeFalse();
});

test('filter status negara dan rentang tanggal menyaring daftar', function () {
    RfqRequest::factory()->create(['country_code' => 'AE', 'status' => RfqStatus::New]);
    RfqRequest::factory()->create(['country_code' => 'DE', 'status' => RfqStatus::New]);
    RfqRequest::factory()->status(RfqStatus::Won)->create(['country_code' => 'AE']);

    $this->actingAs($this->user)
        ->get(route('admin.rfqs.index', ['country_code' => 'AE']))
        ->assertInertia(fn (Assert $page) => $page->has('rfqs.data', 2));

    $this->actingAs($this->user)
        ->get(route('admin.rfqs.index', ['country_code' => 'AE', 'status' => 'new']))
        ->assertInertia(fn (Assert $page) => $page->has('rfqs.data', 1));

    $this->actingAs($this->user)
        ->get(route('admin.rfqs.index', ['from' => now()->addDay()->toDateString()]))
        ->assertInertia(fn (Assert $page) => $page->has('rfqs.data', 0));
});

test('ekspor CSV menghormati filter aktif', function () {
    RfqRequest::factory()->create([
        'company_name' => 'Al Noor Trading',
        'country_code' => 'AE',
    ]);
    RfqRequest::factory()->create([
        'company_name' => 'Berlin Coals',
        'country_code' => 'DE',
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('admin.rfqs.export', ['country_code' => 'AE']));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();

    expect($csv)->toContain('Nomor Referensi')
        ->and($csv)->toContain('Al Noor Trading')
        ->and($csv)->not->toContain('Berlin Coals');
});

test('badge sidebar menghitung RFQ dan pesan belum dibaca', function () {
    RfqRequest::factory()->count(2)->create();
    RfqRequest::factory()->spam()->create();
    ContactMessage::factory()->count(3)->create();
    ContactMessage::factory()->spam()->create();

    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('unread.rfq', 2)
            ->where('unread.contactMessages', 3),
        );
});
