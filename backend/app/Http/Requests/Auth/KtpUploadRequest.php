<?php

// app/Http/Requests/Auth/KtpUploadRequest.php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class KtpUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // jpg, jpeg, png, pdf — max 5MB (5120 KB)
            'ktp_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'ktp_file.required' => 'File KTP wajib diunggah.',
            'ktp_file.file'     => 'Upload harus berupa file.',
            'ktp_file.mimes'    => 'Format file KTP harus JPG, PNG, atau PDF.',
            'ktp_file.max'      => 'Ukuran file KTP tidak boleh lebih dari 5 MB.',
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
