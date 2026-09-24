<?php

// app/Http/Requests/Category/StoreCategoryRequest.php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            // Slug di-generate dari name jika tidak diisi — validasi unique hanya jika diisi eksplisit
            'slug'        => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'string', 'in:active,inactive'],
        ];
    }

    /**
     * Auto-generate slug dari name jika tidak disertakan.
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
