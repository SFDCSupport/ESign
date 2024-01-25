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
                'signers.*.is_deleted' => 'sometimes',
                'signers.*.position' => 'required|integer',
                'signers.*.elements.*.id' => 'sometimes|uuid',
                'signers.*.elements.*.uuid' => 'required',
                'signers.*.elements.*.on_page' => 'required|integer',
                'signers.*.elements.*.left' => 'required',
                'signers.*.elements.*.top' => 'required',
                'signers.*.elements.*.scale_x' => 'sometimes',
                'signers.*.elements.*.scale_y' => 'sometimes',
                'signers.*.elements.*.eleType' => 'required',
                'signers.*.elements.*.width' => 'required',
                'signers.*.elements.*.height' => 'required',
                'signers.*.elements.*.is_required' => 'sometimes',
                'signers.*.elements.*.is_deleted' => 'sometimes',
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
