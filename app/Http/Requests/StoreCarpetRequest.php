<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarpetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uniqueid' => 'required|max:200',
            'name' => 'required|max:200',
            'size' => 'required|max:200',
            'price' => 'required|max:200',
            'phone' => 'required|max:200',
            'location' => 'required|max:400',
            'date_received' => 'required|date',
            'date_delivered' => 'nullable|date',
            'payment_status' => 'required|in:Paid,Not Paid',
            'transaction_code' => 'required_if:payment_status,Paid|nullable|string|max:255',
            'delivered' => 'required|max:200',
            'discount' => 'nullable|numeric|min:0',
        ];
    }
}
