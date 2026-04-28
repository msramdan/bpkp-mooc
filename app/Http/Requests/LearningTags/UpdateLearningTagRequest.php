<?php

namespace App\Http\Requests\LearningTags;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLearningTagRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:2', 'max:120', 'unique:learning_tags,name,'.$this->learning_tag],
        ];
    }
}
