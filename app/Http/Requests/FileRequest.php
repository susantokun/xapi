<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'files.*' => ['nullable', 'mimes:jpeg,png,jpg,webp,pdf,docx,txt', 'max:10240'],
            'details.*' => ['nullable'],
            'files' => ['array', 'max:12'],
            'details' => ['array', 'max:3'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Data gagal ditambahkan!',
            'errors'  => $validator->errors()
        ], 422));
    }
}
