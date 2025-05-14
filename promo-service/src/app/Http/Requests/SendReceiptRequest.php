<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendReceiptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'chek_id'     => 'required|string|max:255|unique:sales_receipts,chek_id',
            'nkm_number'  => 'required|string|max:255',
            'sn'          => 'required|string|max:255',
            'check_date'  => 'required|date',
            'payment_type' => 'required|in:naqt,karta',
            'qqs_summa'   => 'required|numeric|min:0',
            'summa'       => 'required|numeric|min:0',
            'lat'         => 'nullable|numeric|between:-90,90',
            'long'        => 'nullable|numeric|between:-180,180',
            'lang' => ['required', 'string', 'in:uz,ru,kr'],

            // Embedded sales_products
            'products'                 => 'required|array|min:1',
            'products.*.name'         => 'required|string|max:255',
            'products.*.count'        => 'required|integer|min:1',
            'products.*.summa'        => 'required|numeric|min:0',
        ];
    }
    public function messages(): array
    {
        return [
            'chek_id.unique'   => 'Ushbu check ID avval ro‘yxatdan o‘tgan.',
            'products.required'        => 'Hech bo‘lmaganda bitta mahsulot kiritilishi shart.',
            'products.*.name.required' => 'Har bir mahsulot uchun nom kiritilishi shart.',
            'products.*.count.min'     => 'Mahsulot soni kamida 1 bo‘lishi kerak.',
        ];
    }
}
