<?php

namespace App\Http\Requests\API\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:191',
            'email' => [
                'required',
                'email:rfc,dns',
                Rule::unique('users', 'email')->ignoreModel($this->user()),
            ],
            'phone_number' => 'nullable|string',
            'city_id' => [
                Rule::exists('cities', 'id')->where(function ($query) {
                    $query->where('state_id', $this->state_id);
                }),
                'nullable'
            ],
            'country_id' => 'sometimes|nullable|exists:countries,id',
            'state_id' => [
                'sometimes',
                Rule::exists('states', 'id')->where(function ($query) {
                    $query->where('country_id', request()->country_id);
                }),
            ],
            'area' => 'nullable|string',
            'profile_picture' => 'nullable|image',
            'username' => [
                function ($attribute, $value, $fail) {
                    if ($this->user()->username) {
                        $fail('You can not change your username');
                    }
                },
            ]
        ];
    }
}
