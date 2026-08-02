<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseUnitRequest extends FormRequest
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
        $courseUnitId = $this->route('id');

        return [
            'programme_id' => ['required', 'exists:programmes,id'],
            'code' => ['required', 'string', 'max:20', Rule::unique('course_units', 'code')->ignore($courseUnitId)],
            'title' => ['required', 'string', 'max:255'],
            'credit_units' => ['required', 'integer', 'min:1'],
            'year' => ['required', 'integer', 'in:1,2'],
            'semester' => ['required', 'integer', 'in:1,2'],
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
