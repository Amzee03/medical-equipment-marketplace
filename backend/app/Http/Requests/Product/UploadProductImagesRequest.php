<?php

// app/Http/Requests/Product/UploadProductImagesRequest.php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadProductImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images'   => ['required', 'array', 'min:1'],
            // Setiap file: jpg/jpeg/png/webp, max 2MB (2048 KB)
            'images.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required'    => 'Setidaknya satu gambar wajib diunggah.',
            'images.*.mimes'     => 'Format gambar harus JPG, PNG, atau WebP.',
            'images.*.max'       => 'Ukuran setiap gambar tidak boleh lebih dari 2 MB.',
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
