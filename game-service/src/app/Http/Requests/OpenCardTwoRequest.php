<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpenCardTwoRequest extends FormRequest
{
    protected string $resolvedLang = 'uz';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation(): void
    {
        $lang = $this->input('lang', 'uz');
        if (!in_array($lang, ['uz', 'ru', 'kr', 'en'])) {
            $lang = 'uz';
        }

        $this->resolvedLang = $lang;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'session_id' => [
                'required',
                'integer',
                Rule::exists('game_sessions', 'id')->where(
                    fn($q) =>
                    $q->where('status', 'in_progress')
                ),
            ],
            'lang' => ['required', 'in:uz,ru,kr,en'],
            'selected_cards_id' => ['required', 'array'],
            'selected_cards_id.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('game_session_cards', 'id')->where(
                    fn($q) =>
                    $q->where('is_revealed', 0)->where('etap', 2)
                        ->whereRaw('session_id = ?', [$this->session_id])
                ),
            ],
        ];
    }

    /**
     * Custom validation messages based on selected language.
     */
    public function messages(): array
    {
        $lang = $this->resolvedLang;

        $messages = [
            'uz' => [
                'session_id.required' => 'Sessiya ID majburiy.',
                'session_id.integer' => 'Sessiya ID butun son bo‘lishi kerak.',
                'session_id.exists' => 'Bunday faol sessiya topilmadi.',

                'lang.required' => 'Til tanlash majburiy.',
                'lang.in' => 'Tanlangan til noto‘g‘ri.',

                'selected_point.required' => 'Ball tanlash majburiy.',
                'selected_point.integer' => 'Ball butun son bo‘lishi kerak.',
                'selected_point.min' => 'Eng kam ball qiymati 1 bo‘lishi kerak.',

                'selected_cards_id.required' => 'Kartalar ro‘yxati majburiy.',
                'selected_cards_id.array' => 'Kartalar ro‘yxati noto‘g‘ri formatda.',
                'selected_cards_id.*.required' => 'Har bir karta ID kiritilishi kerak.',
                'selected_cards_id.*.integer' => 'Karta ID butun son bo‘lishi kerak.',
                'selected_cards_id.*.distinct' => 'Karta ID lar takrorlanmasligi kerak.',
                'selected_cards_id.*.exists' => 'Karta mavjud emas yoki allaqachon ochilgan.',
            ],

            'ru' => [
                'session_id.required' => 'ID сессии обязателен.',
                'session_id.integer' => 'ID сессии должен быть числом.',
                'session_id.exists' => 'Активная сессия не найдена.',

                'lang.required' => 'Необходимо выбрать язык.',
                'lang.in' => 'Выбран неверный язык.',

                'selected_point.required' => 'Выберите количество очков.',
                'selected_point.integer' => 'Очки должны быть числом.',
                'selected_point.min' => 'Минимальное значение очков — 1.',

                'selected_cards_id.required' => 'Необходимо выбрать карты.',
                'selected_cards_id.array' => 'Список карт имеет неверный формат.',
                'selected_cards_id.*.required' => 'Каждый ID карты обязателен.',
                'selected_cards_id.*.integer' => 'ID карты должен быть числом.',
                'selected_cards_id.*.distinct' => 'ID карт не должны повторяться.',
                'selected_cards_id.*.exists' => 'Карта не существует или уже открыта.',
            ],

            'kr' => [
                'session_id.required' => 'Сессия ID мажбурий.',
                'session_id.integer' => 'Сессия ID бутун сон бўлиши керак.',
                'session_id.exists' => 'Бундай фаол сессия топилмади.',

                'lang.required' => 'Тил танлаш мажбурий.',
                'lang.in' => 'Танланган тил нотўғри.',

                'selected_point.required' => 'Балл танлаш мажбурий.',
                'selected_point.integer' => 'Балл бутун сон бўлиши керак.',
                'selected_point.min' => 'Энг кам балл қиймати 1 бўлиши керак.',

                'selected_cards_id.required' => 'Карталар рўйхати мажбурий.',
                'selected_cards_id.array' => 'Карталар рўйхати нотўғри форматда.',
                'selected_cards_id.*.required' => 'Ҳар бир карта ID киритилиши керак.',
                'selected_cards_id.*.integer' => 'Карта ID бутун сон бўлиши керак.',
                'selected_cards_id.*.distinct' => 'Карта ID лар такрорланмаслиги керак.',
                'selected_cards_id.*.exists' => 'Карта мавжуд эмас ёки аллақачон очилган.',
            ],

            'en' => [
                'session_id.required' => 'Session ID is required.',
                'session_id.integer' => 'Session ID must be an integer.',
                'session_id.exists' => 'Active session not found.',

                'lang.required' => 'Language selection is required.',
                'lang.in' => 'Invalid language selected.',

                'selected_point.required' => 'Point selection is required.',
                'selected_point.integer' => 'Point must be an integer.',
                'selected_point.min' => 'Minimum point value must be 1.',

                'selected_cards_id.required' => 'Card list is required.',
                'selected_cards_id.array' => 'Card list format is invalid.',
                'selected_cards_id.*.required' => 'Each card ID is required.',
                'selected_cards_id.*.integer' => 'Card ID must be an integer.',
                'selected_cards_id.*.distinct' => 'Card IDs must be unique.',
                'selected_cards_id.*.exists' => 'Card not found or already revealed.',
            ],
        ];

        return $messages[$lang] ?? $messages['uz'];
    }
}
