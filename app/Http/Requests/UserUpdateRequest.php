<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
            'first_name'            => ['nullable', 'string', 'max:255'],
            'last_name'             => ['nullable', 'string', 'max:255'],
            'email'                 => ['nullable', 'email', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'phone'                 => ['nullable'],
            'date_of_birth'         => ['nullable', 'date'],
            'avatar'                => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
            'user_name'             => ['nullable'],
            'bio'                   => ['nullable'],
            'email_visibility' => ['nullable', Rule::in(['true', 'false'])],
            'phone_visibility' => ['nullable', Rule::in(['true', 'false'])],
        ];
    }
}
