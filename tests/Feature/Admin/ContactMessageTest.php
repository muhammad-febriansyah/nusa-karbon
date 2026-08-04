<?php

declare(strict_types=1);

use App\Models\ContactMessage;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('tamu tidak bisa mengakses pesan kontak', function () {
    $this->get(route('admin.contact-messages.index'))
        ->assertRedirect(route('login'));
});

test('halaman index memuat pesan beserta jumlah belum dibaca', function () {
    ContactMessage::factory()->count(2)->create();
    ContactMessage::factory()->read()->create();

    $this->actingAs($this->user)
        ->get(route('admin.contact-messages.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/contact-messages/index')
            ->has('messages.data', 3)
            ->where('unreadCount', 2),
        );
});

test('membuka pesan menandainya sudah dibaca', function () {
    $message = ContactMessage::factory()->create();

    $this->actingAs($this->user)
        ->get(route('admin.contact-messages.show', $message))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/contact-messages/show'));

    expect($message->refresh()->is_read)->toBeTrue();
});

test('pesan dapat ditandai spam dan belum dibaca', function () {
    $message = ContactMessage::factory()->read()->create();

    $this->actingAs($this->user)
        ->put(route('admin.contact-messages.update', $message), ['is_spam' => true])
        ->assertRedirect();

    expect($message->refresh()->is_spam)->toBeTrue();

    $this->actingAs($this->user)
        ->put(route('admin.contact-messages.update', $message), ['is_read' => false])
        ->assertRedirect();

    expect($message->refresh()->is_read)->toBeFalse();
});

test('pesan spam tidak ikut hitungan belum dibaca', function () {
    ContactMessage::factory()->create();
    ContactMessage::factory()->spam()->create();

    $this->actingAs($this->user)
        ->get(route('admin.contact-messages.index'))
        ->assertInertia(fn (Assert $page) => $page->where('unreadCount', 1));
});

test('pesan dihapus permanen', function () {
    $message = ContactMessage::factory()->create();

    $this->actingAs($this->user)
        ->delete(route('admin.contact-messages.destroy', $message))
        ->assertRedirect(route('admin.contact-messages.index'));

    expect(ContactMessage::query()->count())->toBe(0);
});

test('filter spam menyaring daftar', function () {
    ContactMessage::factory()->count(2)->create();
    ContactMessage::factory()->spam()->create();

    $this->actingAs($this->user)
        ->get(route('admin.contact-messages.index', ['is_spam' => '1']))
        ->assertInertia(fn (Assert $page) => $page->has('messages.data', 1));

    $this->actingAs($this->user)
        ->get(route('admin.contact-messages.index', ['is_spam' => '0']))
        ->assertInertia(fn (Assert $page) => $page->has('messages.data', 2));
});
