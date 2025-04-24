<?php

namespace App\Http\Requests\Mobil;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],

            'name' => ['required', 'string', 'max:255'],

            'phone' => ['nullable', 'string', 'max:50', 'unique:users,phone'],
            'phone2' => ['nullable', 'string', 'max:50'],

            'gender' => ['nullable', 'in:Erkak,Ayol'], // Male, Female, Unknown

            'is_guest' => ['boolean'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'region_id.exists' => 'Tanlangan viloyat mavjud emas.',
            'district_id.exists' => 'Tanlangan tuman mavjud emas.',
            'phone.unique' => 'Bu telefon raqami allaqachon mavjud.',
            'gender.in' => 'Jins faqat Erkak yoki Ayol bo‘lishi kerak.',
        ];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
}
