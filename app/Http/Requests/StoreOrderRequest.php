<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'             => 'required|in:carpet,laundry',
            'name'             => 'required|string|max:200',
            'phone'            => 'required|string|max:15',
            'location'         => 'nullable|string|max:400',
            'date_received'    => 'required|date',
            'date_delivered'   => 'nullable|date',
            'payment_status'   => 'required|in:Paid,Partial,Not Paid',
            'transaction_code' => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.unique_id'        => 'nullable|string|max:200',
            'items.*.price'            => 'required|numeric|min:0',
            'items.*.discount'         => 'nullable|numeric|min:0',
            // Carpet-specific
            'items.*.size'             => 'nullable|string|max:200',
            'items.*.multiplier'       => 'nullable|numeric|min:0',
            // Laundry-specific
            'items.*.quantity'         => 'nullable|integer|min:1',
            'items.*.item_description' => 'nullable|string|max:500',
            'items.*.weight'           => 'nullable|numeric|min:0',
        ];
    }
}
