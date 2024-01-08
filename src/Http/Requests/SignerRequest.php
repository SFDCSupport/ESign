<?php

namespace NIIT\ESign\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SignerRequest extends FormRequest
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
        $defaultRules = [
            'documentId' => [
                'required',
                Rule::exists('e_documents', 'id'),
            ],
            'mode' => [
                'required',
                Rule::in([
                    'create', 'update', 'bulkDestroy',
                ]),
            ],
        ];

        $mode = $this->request->get('mode');

        if ($mode === 'create') {
            return [];
        }

        if ($mode === 'update') {

        }

        if ($mode === 'bulkDestroy') {
            return [
                'ids' => 'required|array',
                'ids.*' => Rule::exists('e_signers', 'id'),
            ];
        }

        return $defaultRules;
    }
}
