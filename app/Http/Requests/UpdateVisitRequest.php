<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Visit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $visit = $this->route('visit');

        return $visit instanceof Visit && $this->user()->can('update', $visit);
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'started_at' => ['required', 'date', 'before_or_equal:now'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at', 'before_or_equal:now'],
            'notes' => ['nullable', 'string', 'max:20000'],
        ];
    }
}
