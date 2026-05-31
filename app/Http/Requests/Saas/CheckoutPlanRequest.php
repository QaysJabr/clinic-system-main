<?php

namespace App\Http\Requests\Saas;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'billing_cycle' => ['required', Rule::in([Plan::CYCLE_MONTHLY, Plan::CYCLE_YEARLY])],
            'promotion_code' => ['nullable', 'string', 'max:64'],
        ];
    }
}
