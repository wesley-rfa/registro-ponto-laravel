<?php

namespace App\Http\Requests;

use App\Rules\CpfRule;
use App\Rules\CpfUniqueRule;
use App\Rules\CepRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'cpf' => ['required', 'string', 'max:14', new CpfRule(), new CpfUniqueRule()],
            'job_title' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before:today'],
            'postal_code' => ['required', 'string', 'max:10', new CepRule()],
            'address' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'Digite um email válido.',
            'email.unique' => 'Este email já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Este CPF já está em uso.',
            'cpf' => 'O CPF informado não é válido.',
            'birth_date.required' => 'A data de nascimento é obrigatória.',
            'birth_date.before' => 'A data de nascimento deve ser anterior a hoje.',
            'job_title.required' => 'O cargo é obrigatório.',
            'job_title.max' => 'O cargo não pode ter mais de 100 caracteres.',
            'postal_code.required' => 'O CEP é obrigatório.',
            'postal_code.max' => 'O CEP não pode ter mais de 10 caracteres.',
            'address.required' => 'O endereço é obrigatório.',
            'address.max' => 'O endereço não pode ter mais de 255 caracteres.',
        ];
    }
} 