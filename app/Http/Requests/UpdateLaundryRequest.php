<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLaundryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:laundries,id',
            'name' => 'required|string|max:200',
            'phone' => 'required|string|max:15',
            'location' => 'required|string|max:200',
            'unique_id' => 'required|string|max:200',
            'date_received' => 'required|date',
            'date_delivered' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'item_description' => 'required|string|max:200',
            'weight' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'delivered' => 'required|in:Delivered,Not Delivered',
            'payment_status' => 'required|in:Paid,Partial,Not Paid',
            'discount' => 'nullable|numeric|min:0',
        ];
    }
}
