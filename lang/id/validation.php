<?php

declare(strict_types=1);

/*
 * Pesan validasi Bahasa Indonesia.
 *
 * Aturan penulisan (docs/05-design-system.md bagian 11): kalimat pendek, dan
 * error menjelaskan cara memperbaiki, bukan sekadar menyatakan gagal.
 */
return [

    'accepted' => 'Kolom :attribute harus disetujui.',
    'accepted_if' => 'Kolom :attribute harus disetujui bila :other bernilai :value.',
    'active_url' => 'Kolom :attribute bukan URL yang valid.',
    'after' => 'Kolom :attribute harus berisi tanggal setelah :date.',
    'after_or_equal' => 'Kolom :attribute harus berisi tanggal setelah atau sama dengan :date.',
    'alpha' => 'Kolom :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Kolom :attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => 'Kolom :attribute hanya boleh berisi huruf dan angka.',
    'any_of' => 'Kolom :attribute tidak valid.',
    'array' => 'Kolom :attribute harus berupa larik.',
    'ascii' => 'Kolom :attribute hanya boleh berisi karakter dan simbol alfanumerik satu byte.',
    'before' => 'Kolom :attribute harus berisi tanggal sebelum :date.',
    'before_or_equal' => 'Kolom :attribute harus berisi tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => 'Kolom :attribute harus berisi antara :min sampai :max item.',
        'file' => 'Ukuran file :attribute harus antara :min sampai :max kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai antara :min sampai :max.',
        'string' => 'Kolom :attribute harus terdiri dari :min sampai :max karakter.',
    ],
    'boolean' => 'Kolom :attribute harus bernilai benar atau salah.',
    'can' => 'Kolom :attribute berisi nilai yang tidak diizinkan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'contains' => 'Kolom :attribute tidak memuat nilai yang diwajibkan.',
    'current_password' => 'Kata sandi yang Anda masukkan salah.',
    'date' => 'Kolom :attribute bukan tanggal yang valid.',
    'date_equals' => 'Kolom :attribute harus berisi tanggal yang sama dengan :date.',
    'date_format' => 'Format kolom :attribute tidak sesuai dengan :format.',
    'decimal' => 'Kolom :attribute harus memiliki :decimal angka desimal.',
    'declined' => 'Kolom :attribute harus ditolak.',
    'declined_if' => 'Kolom :attribute harus ditolak bila :other bernilai :value.',
    'different' => 'Kolom :attribute dan :other harus berbeda.',
    'digits' => 'Kolom :attribute harus terdiri dari :digits angka.',
    'digits_between' => 'Kolom :attribute harus terdiri dari :min sampai :max angka.',
    'dimensions' => 'Dimensi gambar :attribute tidak sesuai ketentuan.',
    'distinct' => 'Kolom :attribute berisi nilai yang duplikat.',
    'doesnt_contain' => 'Kolom :attribute tidak boleh memuat nilai tersebut.',
    'doesnt_end_with' => 'Kolom :attribute tidak boleh diakhiri dengan salah satu dari: :values.',
    'doesnt_start_with' => 'Kolom :attribute tidak boleh diawali dengan salah satu dari: :values.',
    'email' => 'Kolom :attribute harus berupa alamat email yang valid.',
    'encoding' => 'Kolom :attribute harus memakai enkode :encoding.',
    'ends_with' => 'Kolom :attribute harus diakhiri dengan salah satu dari: :values.',
    'enum' => 'Pilihan :attribute tidak valid.',
    'exists' => 'Pilihan :attribute tidak valid.',
    'extensions' => 'Ekstensi file :attribute harus salah satu dari: :values.',
    'file' => 'Kolom :attribute harus berupa file.',
    'filled' => 'Kolom :attribute wajib diisi.',
    'gt' => [
        'array' => 'Kolom :attribute harus berisi lebih dari :value item.',
        'file' => 'Ukuran file :attribute harus lebih besar dari :value kilobyte.',
        'numeric' => 'Kolom :attribute harus lebih besar dari :value.',
        'string' => 'Kolom :attribute harus lebih panjang dari :value karakter.',
    ],
    'gte' => [
        'array' => 'Kolom :attribute harus berisi :value item atau lebih.',
        'file' => 'Ukuran file :attribute harus :value kilobyte atau lebih.',
        'numeric' => 'Kolom :attribute harus bernilai :value atau lebih.',
        'string' => 'Kolom :attribute harus terdiri dari :value karakter atau lebih.',
    ],
    'hex_color' => 'Kolom :attribute harus berupa warna heksadesimal yang valid.',
    'image' => 'Kolom :attribute harus berupa gambar.',
    'in' => 'Pilihan :attribute tidak valid.',
    'in_array' => 'Kolom :attribute tidak ada di dalam :other.',
    'in_array_keys' => 'Kolom :attribute harus memuat setidaknya satu dari kunci berikut: :values.',
    'integer' => 'Kolom :attribute harus berupa bilangan bulat.',
    'ip' => 'Kolom :attribute harus berupa alamat IP yang valid.',
    'ipv4' => 'Kolom :attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => 'Kolom :attribute harus berupa alamat IPv6 yang valid.',
    'json' => 'Kolom :attribute harus berupa JSON yang valid.',
    'list' => 'Kolom :attribute harus berupa daftar.',
    'lowercase' => 'Kolom :attribute harus ditulis dengan huruf kecil.',
    'lt' => [
        'array' => 'Kolom :attribute harus berisi kurang dari :value item.',
        'file' => 'Ukuran file :attribute harus lebih kecil dari :value kilobyte.',
        'numeric' => 'Kolom :attribute harus lebih kecil dari :value.',
        'string' => 'Kolom :attribute harus lebih pendek dari :value karakter.',
    ],
    'lte' => [
        'array' => 'Kolom :attribute tidak boleh berisi lebih dari :value item.',
        'file' => 'Ukuran file :attribute harus :value kilobyte atau kurang.',
        'numeric' => 'Kolom :attribute harus bernilai :value atau kurang.',
        'string' => 'Kolom :attribute harus terdiri dari :value karakter atau kurang.',
    ],
    'mac_address' => 'Kolom :attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'array' => 'Kolom :attribute tidak boleh berisi lebih dari :max item.',
        'file' => 'Ukuran file :attribute maksimal :max kilobyte. Kompres file terlebih dahulu.',
        'numeric' => 'Kolom :attribute maksimal bernilai :max.',
        'string' => 'Kolom :attribute maksimal :max karakter.',
    ],
    'max_digits' => 'Kolom :attribute maksimal terdiri dari :max angka.',
    'mimes' => 'Format file :attribute belum didukung. Gunakan: :values.',
    'mimetypes' => 'Format file :attribute belum didukung. Gunakan: :values.',
    'min' => [
        'array' => 'Kolom :attribute harus berisi minimal :min item.',
        'file' => 'Ukuran file :attribute minimal :min kilobyte.',
        'numeric' => 'Kolom :attribute minimal bernilai :min.',
        'string' => 'Kolom :attribute minimal :min karakter.',
    ],
    'min_digits' => 'Kolom :attribute minimal terdiri dari :min angka.',
    'missing' => 'Kolom :attribute tidak boleh ada.',
    'missing_if' => 'Kolom :attribute tidak boleh ada bila :other bernilai :value.',
    'missing_unless' => 'Kolom :attribute tidak boleh ada kecuali :other bernilai :value.',
    'missing_with' => 'Kolom :attribute tidak boleh ada bila ada :values.',
    'missing_with_all' => 'Kolom :attribute tidak boleh ada bila ada :values.',
    'multiple_of' => 'Kolom :attribute harus kelipatan dari :value.',
    'not_in' => 'Pilihan :attribute tidak valid.',
    'not_regex' => 'Format kolom :attribute tidak valid.',
    'numeric' => 'Kolom :attribute harus berupa angka.',
    'password' => [
        'letters' => 'Kata sandi harus memuat setidaknya satu huruf.',
        'mixed' => 'Kata sandi harus memuat huruf besar dan huruf kecil.',
        'numbers' => 'Kata sandi harus memuat setidaknya satu angka.',
        'symbols' => 'Kata sandi harus memuat setidaknya satu simbol.',
        'uncompromised' => 'Kata sandi ini pernah bocor di kebocoran data. Pilih kata sandi lain.',
    ],
    'present' => 'Kolom :attribute harus ada.',
    'present_if' => 'Kolom :attribute harus ada bila :other bernilai :value.',
    'present_unless' => 'Kolom :attribute harus ada kecuali :other bernilai :value.',
    'present_with' => 'Kolom :attribute harus ada bila ada :values.',
    'present_with_all' => 'Kolom :attribute harus ada bila ada :values.',
    'prohibited' => 'Kolom :attribute tidak diizinkan.',
    'prohibited_if' => 'Kolom :attribute tidak diizinkan bila :other bernilai :value.',
    'prohibited_if_accepted' => 'Kolom :attribute tidak diizinkan bila :other disetujui.',
    'prohibited_if_declined' => 'Kolom :attribute tidak diizinkan bila :other ditolak.',
    'prohibited_unless' => 'Kolom :attribute tidak diizinkan kecuali :other ada di dalam :values.',
    'prohibits' => 'Kolom :attribute membuat :other tidak diizinkan.',
    'regex' => 'Format kolom :attribute tidak valid.',
    'required' => 'Kolom :attribute wajib diisi.',
    'required_array_keys' => 'Kolom :attribute harus memuat kunci: :values.',
    'required_if' => 'Kolom :attribute wajib diisi bila :other bernilai :value.',
    'required_if_accepted' => 'Kolom :attribute wajib diisi bila :other disetujui.',
    'required_if_declined' => 'Kolom :attribute wajib diisi bila :other ditolak.',
    'required_unless' => 'Kolom :attribute wajib diisi kecuali :other ada di dalam :values.',
    'required_with' => 'Kolom :attribute wajib diisi bila ada :values.',
    'required_with_all' => 'Kolom :attribute wajib diisi bila ada :values.',
    'required_without' => 'Kolom :attribute wajib diisi bila tidak ada :values.',
    'required_without_all' => 'Kolom :attribute wajib diisi bila tidak ada satu pun dari :values.',
    'same' => 'Kolom :attribute dan :other harus sama.',
    'size' => [
        'array' => 'Kolom :attribute harus berisi :size item.',
        'file' => 'Ukuran file :attribute harus :size kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai :size.',
        'string' => 'Kolom :attribute harus terdiri dari :size karakter.',
    ],
    'starts_with' => 'Kolom :attribute harus diawali dengan salah satu dari: :values.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'timezone' => 'Kolom :attribute harus berupa zona waktu yang valid.',
    'unique' => ':attribute ini sudah dipakai. Gunakan yang lain.',
    'uploaded' => 'Gagal mengunggah :attribute. Periksa koneksi lalu coba lagi.',
    'uppercase' => 'Kolom :attribute harus ditulis dengan huruf besar.',
    'url' => 'Format URL :attribute tidak valid.',
    'ulid' => 'Kolom :attribute harus berupa ULID yang valid.',
    'uuid' => 'Kolom :attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Pesan Validasi Khusus
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama Atribut
    |--------------------------------------------------------------------------
    |
    | Label memakai istilah pengguna, bukan nama kolom database.
    | Lihat docs/05-design-system.md bagian 11.
    |
    */

    'attributes' => [
        'name' => 'nama lengkap',
        'email' => 'email',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'current_password' => 'kata sandi saat ini',
        'phone' => 'nomor telepon',
        'avatar' => 'foto profil',
        'code' => 'kode autentikasi',
        'recovery_code' => 'kode pemulihan',
        'remember' => 'ingat saya',
        'token' => 'token',
    ],

];
