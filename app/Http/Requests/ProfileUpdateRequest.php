<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'last_name'   => ['required', 'string', 'max:255'],
            'email'       => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone'         => ['nullable', 'string', 'max:30'],
            'newsletter'    => ['nullable', 'boolean'],
            'whatsapp_link'    => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_facebook'  => ['nullable', 'string', 'max:255'],
            'social_twitter'   => ['nullable', 'string', 'max:255'],
            'social_tiktok'    => ['nullable', 'string', 'max:255'],
            'social_youtube'   => ['nullable', 'string', 'max:255'],
            'website'          => ['nullable', 'string', 'max:255'],
            'bank_holder'      => ['nullable', 'string', 'max:255'],
            'bank_cbu'         => ['nullable', 'string', 'max:22'],
            'bank_alias'       => ['nullable', 'string', 'max:100'],
            'avatar_file'      => ['nullable', 'image', 'max:3072'],
        ];
    }
}
