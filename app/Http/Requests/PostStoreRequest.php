<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PostStoreRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content'      => 'nullable|string',
            'location'     => 'nullable|string|max:255',
            'latitude'     => 'nullable|string',
            'longitude'    => 'nullable|string',
            'images'       => 'nullable|array',
            'images.*'     => 'file',
            'audios'       => 'nullable|array',
            'audios.*'     => 'file',
            'videos'       => 'nullable|array',
            'videos.*'     => 'file',
            'is_draft'     => 'sometimes|boolean',
        ];
    }
}
