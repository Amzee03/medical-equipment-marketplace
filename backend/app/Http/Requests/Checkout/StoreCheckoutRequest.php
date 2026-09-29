<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => [
                'required',
                'integer',
                Rule::exists('addresses', 'id')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                }),
            ],
            'shipping_method' => [
                'required',
                'string',
                Rule::in(['owner_delivery', 'express', 'regular', 'pickup']),
            ],
            'rental_agreement_accepted' => [
                'sometimes', // we will check it manually if rental items exist
                'boolean'
            ],
        ];
    }
}
