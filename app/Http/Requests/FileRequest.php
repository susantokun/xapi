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
            'document_title' => 'required|string|max:255',
            'files.*' => ['required', 'file', 'mimes:pdf,txt,doc,docx', 'max:102400'],
            'details.*' => ['nullable'],
            'files' => ['required', 'array', 'max:12'],
            'details' => ['nullable', 'array', 'max:3'],
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
