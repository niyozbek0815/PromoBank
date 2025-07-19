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
            'region_id'   => ['required', 'integer', 'exists:regions,id'],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'name'        => ['required', 'string', 'max:255'],
            'phone2'      => ['required', 'string', 'regex:/^\+998\d{9}$/', 'max:14'],
            'gender'      => ['required', 'in:e,a'],
            'birthdate'   => ['required', 'date_format:Y-m-d'],
            'avatar'      => ['nullable', 'string', function ($attribute, $value, $fail) {
                // 1. Base64 formatini tekshir
                if (! preg_match('/^data:image\/(jpg|jpeg|png|webp);base64,/', $value)) {
                    return $fail('Avatar maydonida faqat JPG, JPEG, PNG yoki WEBP formatdagi base64 rasm yuborilishi kerak.');
                }

                // 2. Base64 ni dekodlab rasm hajmini tekshir
                $sizeInBytes = (int) (strlen(base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $value))) ?? 0);

                if ($sizeInBytes > 512 * 1024) { // 512 KB
                    return $fail('Avatar hajmi 512KB dan oshmasligi kerak.');
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'region_id.exists'   => 'Tanlangan viloyat mavjud emas.',
            'district_id.exists' => 'Tanlangan tuman mavjud emas.',
            'gender.in'          => 'Jins faqat Erkak yoki Ayol bo‘lishi kerak.',
            'phone.regex'        => 'Telefon raqami +998 bilan boshlanishi va jami 13 ta belgidan iborat bo‘lishi kerak.',
            'phone2.regex'       => 'Qo‘shimcha raqam +998 bilan boshlanishi va jami 13 ta belgidan iborat bo‘lishi kerak.',
        ];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
}