<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Actions\Auth\SendTwoFactorEmailCode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class TwoFactorEmailChallengeRequest extends FormRequest
{
    /**
     * Only a session that is mid-2FA (primary auth already passed) may verify.
     */
    public function authorize(): bool
    {
        return $this->session()->has('login.id');
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $cacheKey = SendTwoFactorEmailCode::cacheKey($this->session()->get('login.id'));
                $hashedCode = Cache::get($cacheKey);

                if (! is_string($hashedCode) || ! Hash::check((string) $this->input('code'), $hashedCode)) {
                    $validator->errors()->add('code', __('The provided code is invalid or has expired.'));
                }
            },
        ];
    }
}
