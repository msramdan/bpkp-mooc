<?php

namespace App\Http\Requests\Courses;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('course enrollment manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = User::query()->find($this->input('user_id'));

            if ($user && ! $user->hasRole('peserta')) {
                $validator->errors()->add('user_id', __('Pengguna harus memiliki peran peserta.'));
            }
        });
    }
}
