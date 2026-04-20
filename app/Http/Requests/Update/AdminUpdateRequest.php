<?php

namespace App\Http\Requests\Update;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $fuelStatuses = ['available', 'limited', 'unavailable'];

        return [
            'petrol'     => ['nullable', 'in:' . implode(',', $fuelStatuses)],
            'diesel'     => ['nullable', 'in:' . implode(',', $fuelStatuses)],
            'kerosene'   => ['nullable', 'in:' . implode(',', $fuelStatuses)],
            'gas'        => ['nullable', 'in:' . implode(',', $fuelStatuses)],
            'congestion' => ['required', 'in:low,medium,high'],
        ];
    }
}
