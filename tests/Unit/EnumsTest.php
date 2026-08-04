<?php

declare(strict_types=1);

use App\Enums\DocumentType;
use App\Enums\GalleryItemType;
use App\Enums\Incoterm;
use App\Enums\PageBlockType;
use App\Enums\PostStatus;
use App\Enums\RfqStatus;

test('nilai enum cocok dengan kolom ENUM di docs/04-erd.md', function (string $enum, array $expected) {
    expect(array_column($enum::cases(), 'value'))->toBe($expected);
})->with([
    [RfqStatus::class, ['new', 'contacted', 'quoted', 'negotiating', 'won', 'lost', 'spam']],
    [PostStatus::class, ['draft', 'scheduled', 'published', 'archived']],
    [DocumentType::class, ['coa', 'msds', 'sht', 'packing_list', 'other']],
    [Incoterm::class, ['FOB', 'CIF', 'CFR', 'EXW', 'OTHER']],
    [PageBlockType::class, ['rich_text', 'text_image', 'list', 'stat']],
    [GalleryItemType::class, ['image', 'video']],
]);

test('setiap enum punya label untuk seluruh case', function (string $enum) {
    foreach ($enum::cases() as $case) {
        expect($case->label())->not->toBe('');
    }
})->with([
    RfqStatus::class,
    PostStatus::class,
    DocumentType::class,
    Incoterm::class,
    PageBlockType::class,
    GalleryItemType::class,
]);

test('options mengembalikan pasangan value dan label', function () {
    expect(RfqStatus::options())->toHaveCount(7)
        ->and(RfqStatus::options()[0])->toBe(['value' => 'new', 'label' => 'Baru']);
});

test('istilah perdagangan tidak diterjemahkan', function () {
    expect(Incoterm::Fob->label())->toBe('FOB')
        ->and(Incoterm::Cif->label())->toBe('CIF')
        ->and(DocumentType::Coa->label())->toBe('COA')
        ->and(DocumentType::Msds->label())->toBe('MSDS')
        ->and(DocumentType::Sht->label())->toBe('SHT');
});

test('warna badge RFQ mengikuti docs/05-design-system.md bagian 8', function () {
    expect(RfqStatus::New->color())->toBe('primary')
        ->and(RfqStatus::Contacted->color())->toBe('info')
        ->and(RfqStatus::Quoted->color())->toBe('warning')
        ->and(RfqStatus::Negotiating->color())->toBe('warning')
        ->and(RfqStatus::Won->color())->toBe('success')
        ->and(RfqStatus::Lost->color())->toBe('neutral')
        ->and(RfqStatus::Spam->color())->toBe('destructive');
});
