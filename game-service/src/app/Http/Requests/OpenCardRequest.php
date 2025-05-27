<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpenCardRequest extends FormRequest
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
            'session_id' => [
                'required',
                'integer',
                Rule::exists('game_sessions', 'id')->where(function ($query) {
                    $query->where('status', 'in_progress');
                    //   ->orWhere('status', 'waiting_for_stage2');
                }),
            ],
            'selected_point' => ['required', 'integer', 'min:1'], // yoki siz istagan son
            'selected_cards_id' => ['required', 'array'], // yoki siz istagan son
            'selected_cards_id.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('game_session_cards', 'id')
                    ->where(function ($query) {
                        $query->where('is_revealed', 0)
                            ->whereRaw('session_id = ?', [$this->session_id]);
                    }),
            ],
        ];
    }
}
