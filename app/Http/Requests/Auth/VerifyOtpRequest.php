<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'phone'        => ['required', 'string', 'regex:/^\+?[0-9]{7,15}$/'],
            'code'         => ['required', 'string', 'size:6'],
            'device_token' => ['nullable', 'string'],
        ];
    }
}
