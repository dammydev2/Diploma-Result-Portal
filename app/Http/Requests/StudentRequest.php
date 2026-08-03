<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
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
        $studentId = $this->route('id');

        return [
            'programme_id' => ['required', 'exists:programmes,id'],
            'name' => ['required', 'string', 'max:255'],
            'matric_no' => ['required', 'string', 'max:50', Rule::unique('students', 'matric_no')->ignore($studentId)],
            'year_of_admission' => ['required', 'integer', 'min:2000', 'max:'.now()->year],
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
