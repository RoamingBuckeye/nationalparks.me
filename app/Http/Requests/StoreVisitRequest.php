<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'park_id' => ['required', 'integer', 'exists:parks,id'],
            'started_at' => ['nullable', 'date', 'before_or_equal:now'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at', 'before_or_equal:now'],
            'notes' => ['nullable', 'string', 'max:20000'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->filled('ended_at') && ! $this->filled('started_at')) {
                    $validator->errors()->add('started_at', __('A start date is required when an end date is given.'));
                }
            },
        ];
    }
}
