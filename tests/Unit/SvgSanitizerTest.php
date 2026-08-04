<?php

declare(strict_types=1);

use App\Support\SvgSanitizer;

beforeEach(function () {
    $this->sanitizer = new SvgSanitizer;
});

test('elemen script dibuang', function () {
    $svg = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <script>alert('xss')</script>
        <path d="M0 0h24v24H0z"/>
    </svg>
    SVG;

    $clean = $this->sanitizer->sanitize($svg);

    expect($clean)->not->toBeNull()
        ->and($clean)->not->toContain('script')
        ->and($clean)->not->toContain('alert')
        ->and($clean)->toContain('<path');
});

test('atribut handler event dibuang', function () {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg"><circle cx="5" cy="5" r="5" onload="alert(1)" onclick="steal()"/></svg>';

    $clean = $this->sanitizer->sanitize($svg);

    expect($clean)->not->toContain('onload')
        ->not->toContain('onclick')
        ->not->toContain('alert')
        ->and($clean)->toContain('<circle');
});

test('protokol javascript pada atribut dibuang', function () {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0" fill="javascript:alert(1)"/></svg>';

    $clean = $this->sanitizer->sanitize($svg);

    expect($clean)->not->toContain('javascript:');
});

test('elemen di luar daftar putih dibuang', function () {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg"><foreignObject><iframe src="https://jahat.test"></iframe></foreignObject><rect width="10" height="10"/></svg>';

    $clean = $this->sanitizer->sanitize($svg);

    expect($clean)->not->toContain('foreignObject')
        ->not->toContain('iframe')
        ->and($clean)->toContain('<rect');
});

test('atribut menggambar yang sah dipertahankan', function () {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2L2 7" stroke="#F93E00" stroke-width="2" fill="none"/></svg>';

    $clean = $this->sanitizer->sanitize($svg);

    expect($clean)->toContain('viewBox')
        ->toContain('d="M12 2L2 7"')
        ->toContain('stroke="#F93E00"')
        ->toContain('stroke-width="2"');
});

test('berkas yang bukan svg ditolak', function (string $input) {
    expect($this->sanitizer->sanitize($input))->toBeNull();
})->with([
    'kosong' => '',
    'bukan xml' => 'ini teks biasa',
    'html' => '<html><body>halo</body></html>',
]);
