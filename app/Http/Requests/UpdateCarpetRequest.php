<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarpetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:carpets,id',
            'uniqueid' => 'required|string|max:200',
            'name' => 'required|string|max:200',
            'size' => 'required|string|max:200',
            'price' => 'required|numeric|min:0',
            'phone' => 'required|string|max:15',
            'location' => 'required|string|max:400',
            'date_received' => 'required|date',
            'date_delivered' => 'required|date',
            'payment_status' => 'required|in:Paid,Not Paid',
            'transaction_code' => 'nullable|string|max:255',
            'delivered' => 'required|in:Delivered,Not Delivered',
            'discount' => 'nullable|numeric|min:0',
        ];
    }
}
