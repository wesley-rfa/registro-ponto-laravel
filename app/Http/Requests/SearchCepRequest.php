<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchCepRequest extends FormRequest
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
            'cep' => 'required|string|min:8|max:10'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'cep.required' => 'O CEP é obrigatório',
            'cep.string' => 'O CEP deve ser uma string',
            'cep.min' => 'O CEP deve ter pelo menos 8 caracteres',
            'cep.max' => 'O CEP deve ter no máximo 10 caracteres'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cep = $this->input('cep');
            
            if ($cep) {
                $cepLimpo = preg_replace('/[^0-9]/', '', $cep);
                
                if (strlen($cepLimpo) !== 8) {
                    $validator->errors()->add('cep', 'O CEP deve conter exatamente 8 dígitos numéricos');
                }
            }
        });
    }
} 