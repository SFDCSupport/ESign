<?php

namespace NIIT\ESign\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SignerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $defaultRules = [
            'document_id' => [
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
            return [
                'signers.*.id' => 'sometimes|uuid',
                'signers.*.uuid' => 'required',
                'signers.*.label' => 'required',
                'signers.*.position' => 'required|integer',
                'signers.*.elements.*.id' => 'sometimes|uuid',
                'signers.*.elements.*.uuid' => 'required',
                'signers.*.elements.*.on_page' => 'required|integer',
                'signers.*.elements.*.offset_x' => 'required',
                'signers.*.elements.*.offset_y' => 'required',
                'signers.*.elements.*.type' => 'required',
                'signers.*.elements.*.width' => 'required',
                'signers.*.elements.*.height' => 'required',
                'signers.*.elements.*.is_required' => 'sometimes',
            ];
        }

        if ($mode === 'update') {

        }

        if ($mode === 'bulkDestroy') {
            return [
                'ids' => 'required|array',
                'ids.*' => Rule::exists('e_document_signers', 'id'),
            ];
        }

        return $defaultRules;
    }
}
