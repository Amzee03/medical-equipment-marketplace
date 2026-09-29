<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type'       => ['required', 'string', 'in:purchase,rental'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'rental_start_date' => [
                'required_if:type,rental', 
                'nullable', 
                'date_format:Y-m-d', 
                'after_or_equal:today'
            ],
            'rental_end_date'   => [
                'required_if:type,rental', 
                'nullable', 
                'date_format:Y-m-d', 
                'after_or_equal:rental_start_date'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rental_start_date.required_if' => 'Tanggal mulai sewa wajib diisi untuk item penyewaan.',
            'rental_start_date.after_or_equal' => 'Tanggal mulai sewa tidak boleh sebelum hari ini.',
            'rental_end_date.required_if' => 'Tanggal selesai sewa wajib diisi untuk item penyewaan.',
            'rental_end_date.after_or_equal' => 'Tanggal selesai sewa tidak boleh sebelum tanggal mulai.',
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Data tidak valid.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
