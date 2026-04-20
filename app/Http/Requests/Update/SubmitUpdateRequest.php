<?php

namespace App\Http\Requests\Update;

use Illuminate\Foundation\Http\FormRequest;

class SubmitUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $fuelStatuses = ['available', 'limited', 'unavailable'];

        return [
            'petrol'          => ['nullable', 'in:' . implode(',', $fuelStatuses)],
            'petrol_normal'   => ['nullable', 'in:' . implode(',', $fuelStatuses)],
            'petrol_improved' => ['nullable', 'in:' . implode(',', $fuelStatuses)],
            'petrol_super'    => ['nullable', 'in:' . implode(',', $fuelStatuses)],
            'diesel'          => ['nullable', 'in:' . implode(',', $fuelStatuses)],
            'kerosene'        => ['nullable', 'in:' . implode(',', $fuelStatuses)],
            'gas'             => ['nullable', 'in:' . implode(',', $fuelStatuses)],
            'congestion'      => ['nullable', 'in:low,medium,high'],
            'device_id'       => ['nullable', 'string', 'max:255'],
        ];
    }
}
