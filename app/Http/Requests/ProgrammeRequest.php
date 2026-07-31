<?php

namespace App\Http\Requests;

use App\Enums\ProgrammeLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgrammeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $programmeId = $this->route('id');

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('programmes', 'name')
                    ->where(fn ($query) => $query->where('level', $this->input('level')))
                    ->ignore($programmeId),
            ],
            'code' => ['required', 'string', 'max:20', Rule::unique('programmes', 'code')->ignore($programmeId)],
            'level' => ['required', Rule::enum(ProgrammeLevel::class)],
            'min_credit_units' => ['nullable', 'integer', 'min:1'],
            'max_credit_units' => ['nullable', 'integer', 'gte:min_credit_units'],
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
