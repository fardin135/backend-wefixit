<?php

namespace App\Http\Requests\Auth;

use App\Traits\APIResponses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class AuthRequest extends FormRequest
{
    use APIResponses;
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
            'email' => 'required_without:username|email|min:8|max:255',
            'username' => 'required_without:email|string|min:3|max:255',
            'password' => 'required|string|max:255',
            'remember' => 'boolean',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = $this->validationError(
            $validator->errors(),
        );

        throw new ValidationException($validator, $response);
    }
}