<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity'   => ['sometimes', 'required', 'integer', 'min:1'],
            'rental_start_date' => [
                'sometimes',
                'required', 
                'date_format:Y-m-d', 
                'after_or_equal:today',
            ],
            // Only validate after_or_equal if both dates are provided in request,
            // otherwise we'd need more complex validation logic depending on existing data.
            'rental_end_date'   => [
                'sometimes',
                'required', 
                'date_format:Y-m-d', 
                'after_or_equal:rental_start_date'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rental_start_date.after_or_equal' => 'Tanggal mulai sewa tidak boleh sebelum hari ini.',
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
