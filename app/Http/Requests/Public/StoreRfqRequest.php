<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use App\Enums\Incoterm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Permintaan penawaran dari pengunjung.
 *
 * Yang diwajibkan hanya empat: perusahaan, nama, email, dan negara tujuan.
 * Tanpa keempatnya tim sales tidak bisa membalas sama sekali. Sisanya
 * membantu penawaran jadi lebih tepat, tetapi mewajibkannya membuat pembeli
 * yang baru menjajaki batal mengirim — dan lead yang batal terkirim jauh lebih
 * mahal daripada lead yang datanya kurang lengkap.
 */
final class StoreRfqRequest extends FormRequest
{
    /**
     * Rute publik; siapa pun boleh mengirim.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            /*
             * `rfc` saja, tanpa `dns`. Pemeriksaan DNS membuat setiap pengiriman
             * menunggu kueri jaringan, dan kegagalan DNS sesaat akan menolak
             * pembeli yang alamatnya sebenarnya benar. Pada formulir yang satu
             * lead-nya berharga, menolak alamat sah jauh lebih mahal daripada
             * menerima satu alamat yang salah ketik — dan salah ketik akan
             * ketahuan sendiri saat email balasan memantul.
             */
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'country_code' => ['required', 'string', 'size:2', 'alpha'],
            'incoterms' => ['nullable', Rule::enum(Incoterm::class)],
            'port_of_destination' => ['nullable', 'string', 'max:255'],
            'monthly_tonnage' => ['nullable', 'string', 'max:100'],
            'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where('is_active', true)],
            'container_type_id' => ['nullable', 'integer', Rule::exists('container_types', 'id')->where('is_active', true)],
            'packaging_notes' => ['nullable', 'string', 'max:2000'],
            'message' => ['nullable', 'string', 'max:5000'],

            /*
             * Umpan robot. Bidang ini disembunyikan dari pengunjung lewat CSS
             * dan `tabindex="-1"`, jadi manusia tidak pernah mengisinya. Robot
             * pengisi form otomatis mengisi setiap input yang ditemukannya.
             *
             * Divalidasi sebagai "harus kosong", bukan disaring diam-diam,
             * supaya percobaan spam berhenti di lapisan validasi dan tidak
             * pernah menyentuh basis data.
             */
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'company_name' => 'nama perusahaan',
            'contact_name' => 'nama Anda',
            'email' => 'email',
            'country_code' => 'kode negara',
            'port_of_destination' => 'pelabuhan tujuan',
            'monthly_tonnage' => 'perkiraan volume',
            'product_id' => 'produk',
            'container_type_id' => 'jenis kontainer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'country_code.size' => 'Kode negara memakai dua huruf, misalnya ID, SA, atau RU.',
            'country_code.alpha' => 'Kode negara memakai dua huruf, misalnya ID, SA, atau RU.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $country = $this->input('country_code');

        if (is_string($country)) {
            $this->merge(['country_code' => mb_strtoupper(trim($country))]);
        }
    }
}
