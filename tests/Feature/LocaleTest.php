<?php

declare(strict_types=1);

use App\Support\Locale;

/*
 * Untuk sekarang hanya Bahasa Indonesia. Test ini membaca `config/locales.php`
 * apa adanya, jadi saat locale lain dihidupkan di Fase 6 cukup memperbarui
 * dua test pertama.
 */
test('locale yang didukung sesuai config', function () {
    expect(Locale::default())->toBe('id')
        ->and(Locale::supported())->toBe(['id'])
        ->and(Locale::isSupported('id'))->toBeTrue()
        ->and(Locale::isSupported('de'))->toBeFalse();
});

test('Bahasa Indonesia memakai arah baca ltr', function () {
    expect(Locale::direction('id'))->toBe('ltr')
        ->and(Locale::isRtl('id'))->toBeFalse();
});

test('locale tak dikenal dianggap ltr', function () {
    expect(Locale::direction('de'))->toBe('ltr')
        ->and(Locale::meta('de'))->toBeNull();
});

test('bahasa default tidak memakai prefix url', function () {
    expect(Locale::urlPrefix('id'))->toBe('')
        ->and(Locale::urlPrefix('ar'))->toBe('ar');
});
