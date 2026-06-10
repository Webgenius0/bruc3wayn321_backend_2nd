<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PostUpdateRequest extends FormRequest
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
            'content' => 'sometimes|nullable|string',

            'location' => 'sometimes|nullable|string|max:255',

            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',

            'images' => 'sometimes|nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',

            'audios' => 'sometimes|nullable|array',
            'audios.*' => 'file|mimes:mp3,wav,m4a,aac|max:10240',

            'videos' => 'sometimes|nullable|array',
            'videos.*' => 'file|mimes:mp4,mov,avi,mkv,webm|max:51200',

            // 'is_draft' => 'sometimes|boolean',
            'is_published' => 'sometimes|boolean',
        ];
    }
}
