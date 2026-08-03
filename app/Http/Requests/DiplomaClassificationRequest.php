<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiplomaClassificationRequest extends FormRequest
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
        $diplomaClassificationId = $this->route('id');

        return [
            'classification' => ['required', 'string', 'max:50', Rule::unique('diploma_classifications', 'classification')->ignore($diplomaClassificationId)],
            'min_cgpa' => ['required', 'numeric', 'min:0', 'max:4', 'lte:max_cgpa'],
            'max_cgpa' => ['required', 'numeric', 'min:0', 'max:4', 'gte:min_cgpa'],
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
