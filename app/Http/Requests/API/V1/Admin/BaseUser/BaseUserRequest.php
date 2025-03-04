<?php

namespace App\Http\Requests\API\V1\Admin\BaseUser;

use Illuminate\Foundation\Http\FormRequest;

class BaseUserRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // 'email' => 'required|email|unique:users,email|max:255',
            'phone' => 'nullable|string|max:20',
            // 'status' => 'required',
        ];
    }
}
