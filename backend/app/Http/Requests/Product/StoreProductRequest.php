<?php

// app/Http/Requests/Product/StoreProductRequest.php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'              => ['required', 'integer', 'exists:categories,id'],
            'name'                     => ['required', 'string', 'max:255'],
            'slug'                     => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'sku'                      => ['required', 'string', 'max:100', 'unique:products,sku'],
            'description'              => ['nullable', 'string'],
            'function'                 => ['nullable', 'string'],
            'brand'                    => ['nullable', 'string', 'max:255'],
            'model'                    => ['nullable', 'string', 'max:255'],
            // specifications adalah objek JSON bebas (spesifikasi teknis alat medis)
            'specifications'           => ['nullable', 'array'],
            'condition'                => ['required', 'string', 'in:baru,bekas_baik,perlu_pemeriksaan'],
            'purchase_available'       => ['required', 'boolean'],
            'sale_price'               => ['nullable', 'numeric', 'min:0', 'required_if:purchase_available,true'],
            'stock_purchase'           => ['nullable', 'integer', 'min:0'],
            'rental_available'         => ['required', 'boolean'],
            'rental_price_daily'       => ['nullable', 'numeric', 'min:0', 'required_if:rental_available,true'],
            'rental_price_weekly'      => ['nullable', 'numeric', 'min:0'],
            'rental_price_monthly'     => ['nullable', 'numeric', 'min:0'],
            'min_rental_days'          => ['nullable', 'integer', 'min:1', 'required_if:rental_available,true'],
            'max_rental_days'          => ['nullable', 'integer', 'min:1', 'gte:min_rental_days', 'required_if:rental_available,true'],
            'shipping_owner_delivery'  => ['required', 'boolean'],
            'shipping_express'         => ['required', 'boolean'],
            'shipping_regular'         => ['required', 'boolean'],
            'shipping_pickup'          => ['required', 'boolean'],
            'status'                   => ['required', 'string', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_price.required_if'         => 'Harga jual wajib diisi jika produk tersedia untuk pembelian.',
            'rental_price_daily.required_if' => 'Harga sewa harian wajib diisi jika produk tersedia untuk disewa.',
            'min_rental_days.required_if'    => 'Minimum hari sewa wajib diisi jika produk tersedia untuk disewa.',
            'max_rental_days.required_if'    => 'Maksimum hari sewa wajib diisi jika produk tersedia untuk disewa.',
            'max_rental_days.gte'            => 'Maksimum hari sewa harus lebih besar atau sama dengan minimum hari sewa.',
        ];
    }

    /**
     * Auto-generate slug dari name jika tidak diisi.
     */
    protected function prepareForValidation(): void
    {
        if (empty($this->slug) && $this->name) {
            $this->merge(['slug' => Str::slug($this->name)]);
        }
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Data yang diberikan tidak valid.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
