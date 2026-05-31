<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendGeneralChatMessageRequest extends FormRequest
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
            'body' => ['required', 'string', 'max:2000'],
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ];
    }
}
