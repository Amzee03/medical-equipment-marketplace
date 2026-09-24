<?php

// app/Http/Requests/Product/UpdateEquipmentUnitRequest.php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEquipmentUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'condition' => ['sometimes', 'required', 'string', 'in:baik,rusak_ringan,rusak_berat,hilang'],
            // 'retired' tidak boleh di-set via PATCH ini — gunakan endpoint DELETE (soft-retire)
            'status'    => ['sometimes', 'required', 'string', 'in:available,rented,maintenance'],
            'notes'     => ['nullable', 'string'],
        ];
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
