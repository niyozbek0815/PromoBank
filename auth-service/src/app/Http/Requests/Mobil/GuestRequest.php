<?php
namespace App\Http\Requests\Mobil;

use Illuminate\Foundation\Http\FormRequest;

class GuestRequest extends FormRequest
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
            'uuid'         => 'required|string',
            'model'        => "required|string",
            'platform'     => "required|string|in:ios,android,linux",
            'device_token' => 'required|string|max:255',
            'app_version'  => 'nullable|string|max:100',
        ];
    }
}
