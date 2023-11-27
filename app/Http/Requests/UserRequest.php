<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
            'nombre'           => 'nullable',
            'email'            => ['required', $this->usuario ? Rule::unique('users', 'email')->ignore($this->usuario) : 'unique:users,email'],
            'rol_id'           => 'required',
        ];
    }


    public function messages() : array
    {
        return [
            'email.unique'    => 'El email ya está siendo usado, el mismo debe ser único',
            'rol_id.required' => 'El rol es importante no lo olvides',
            'email.required'  => 'Este campo es obligatorio',
            'email.email'     => 'El email no es valido por favor verifique',
            'email.unique'    => 'El email debe ser único ya otro usuario lo esta usando.',
        ];
    }
}
