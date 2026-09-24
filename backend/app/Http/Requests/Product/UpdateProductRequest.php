<?php

// app/Http/Requests/Product/UpdateProductRequest.php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'category_id'              => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'name'                     => ['sometimes', 'required', 'string', 'max:255'],
            'slug'                     => ['nullable', 'string', 'max:255', "unique:products,slug,{$id}"],
            'sku'                      => ['sometimes', 'required', 'string', 'max:100', "unique:products,sku,{$id}"],
            'description'              => ['nullable', 'string'],
            'function'                 => ['nullable', 'string'],
            'brand'                    => ['nullable', 'string', 'max:255'],
            'model'                    => ['nullable', 'string', 'max:255'],
            'specifications'           => ['nullable', 'array'],
            'condition'                => ['sometimes', 'required', 'string', 'in:baru,bekas_baik,perlu_pemeriksaan'],
            'purchase_available'       => ['sometimes', 'required', 'boolean'],
            'sale_price'               => ['nullable', 'numeric', 'min:0'],
            'stock_purchase'           => ['nullable', 'integer', 'min:0'],
            'rental_available'         => ['sometimes', 'required', 'boolean'],
            'rental_price_daily'       => ['nullable', 'numeric', 'min:0'],
            'rental_price_weekly'      => ['nullable', 'numeric', 'min:0'],
            'rental_price_monthly'     => ['nullable', 'numeric', 'min:0'],
            'min_rental_days'          => ['nullable', 'integer', 'min:1'],
            'max_rental_days'          => ['nullable', 'integer', 'min:1', 'gte:min_rental_days'],
            'shipping_owner_delivery'  => ['sometimes', 'required', 'boolean'],
            'shipping_express'         => ['sometimes', 'required', 'boolean'],
            'shipping_regular'         => ['sometimes', 'required', 'boolean'],
            'shipping_pickup'          => ['sometimes', 'required', 'boolean'],
            'status'                   => ['sometimes', 'required', 'string', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'max_rental_days.gte' => 'Maksimum hari sewa harus lebih besar atau sama dengan minimum hari sewa.',
        ];
    }

    /**
     * Auto-generate slug dari name jika name berubah tapi slug tidak diisi.
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
