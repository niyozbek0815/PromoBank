<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;

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
            'chek_id' => [
                'required',
                'string',
                Rule::unique('sales_receipts', 'chek_id')
            ],            'nkm_number'  => 'required|string|max:255',
            'sn'          => 'required|string|max:255',
            'check_date'  => 'required|date',
            'qqs_summa'   => 'required|numeric|min:0',
            'summa'       => 'required|numeric|min:0',
            'lat'         => 'nullable|numeric|between:-90,90',
            'long'        => 'nullable|numeric|between:-180,180',
            'lang' => ['required', 'string', 'in:uz,ru,kr,en'],

            // Embedded sales_products
            'products'                 => 'required|array|min:1',
            'products.*.name'         => 'required|string|max:255',
            'products.*.count'        => 'required|numeric|min:0.000000001',
            'products.*.summa'        => 'required|numeric|min:0',
        ];
    }
    public function messages(): array
    {
        $lang = $this->input('lang', 'uz');

        $messages = [
            'uz' => [
                'chek_id.unique' => 'Ushbu check ID avval ro‘yxatdan o‘tgan.',
                'products.required' => 'Hech bo‘lmaganda bitta mahsulot kiritilishi shart.',
                'products.*.name.required' => 'Har bir mahsulot uchun nom kiritilishi shart.',
                'products.*.count.min' => 'Mahsulot miqdori kamida 1 bo‘lishi kerak.',
                'lang.in' => 'Til faqat uz, ru, en yoki kr bo‘lishi mumkin.',
            ],
            'ru' => [
                'chek_id.unique' => 'Этот чек уже был зарегистрирован ранее.',
                'products.required' => 'Необходимо указать хотя бы один продукт.',
                'products.*.name.required' => 'Для каждого продукта нужно указать название.',
                'products.*.count.min' => 'Количество товара должно быть не менее 1.',
                'lang.in' => 'Язык должен быть uz, ru, en или kr.',
            ],
            'en' => [
                'chek_id.unique' => 'This check ID has already been registered.',
                'products.required' => 'At least one product must be provided.',
                'products.*.name.required' => 'Each product must have a name.',
                'products.*.count.min' => 'Product quantity must be at least 1.',
                'lang.in' => 'Language must be one of: uz, ru, en, kr.',
            ],
            'kr' => [
                'chek_id.unique' => '이 영수증 ID는 이미 등록되었습니다.',
                'products.required' => '최소 한 개의 상품을 입력해야 합니다.',
                'products.*.name.required' => '각 상품의 이름을 입력해야 합니다.',
                'products.*.count.min' => '상품 수량은 최소 1이어야 합니다.',
                'lang.in' => '언어는 uz, ru, en, kr 중 하나여야 합니다.',
            ],
        ];

        return $messages[$lang] ?? $messages['uz']; // fallback: uz
    }
}
