<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isVerificationPing = $this->integer('type') === 1;
        $isHookPing = $this->integer('type') === 2;

        return [
            'guild_id' => [$isVerificationPing ? 'nullable' : 'required', 'string'],
            'channel_id' => [$isVerificationPing ? 'nullable' : 'required', 'string'],
            'data.options.0.value' => [$isHookPing ? 'required' : 'nullable', 'string'],
        ];
    }
}
