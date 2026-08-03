<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradingScaleRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $gradingScaleId = $this->route('id');

        return [
            'letter_grade' => ['required', 'string', 'max:10', Rule::unique('grading_scales', 'letter_grade')->ignore($gradingScaleId)],
            'min_score' => ['required', 'integer', 'min:0', 'max:100', 'lte:max_score'],
            'max_score' => ['required', 'integer', 'min:0', 'max:100', 'gte:min_score'],
            'weighting' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array<string, string>
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
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
