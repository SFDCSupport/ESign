<?php

namespace NIIT\ESign\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SigningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update_signer');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'documentId' => [
                'required',
                Rule::exists('e_documents', 'id'),
            ],
            'signerId' => [
                'required',
                Rule::exists('e_document_signers', 'id'),
            ],
            'mode' => [
                'required',
                Rule::in([
                    'save', 'draft',
                ]),
            ],
        ];
    }
}
