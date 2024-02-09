<?php

namespace NIIT\ESign\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\NotificationSequence;

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
        $additionalRules = [];
        $mode = $this->request->get('mode');

        $additionalRules = [
            'signers.*.id' => 'sometimes|uuid',
            'signers.*.uuid' => 'required',
            'signers.*.text' => 'required',
            'signers.*.email' => 'nullable|email',
            'signers.*.is_deleted' => 'sometimes',
            'signers.*.position' => 'required|integer',
            'signers.*.elements.*.id' => 'sometimes|uuid',
            'signers.*.elements.*.uuid' => 'required',
            'signers.*.elements.*.text' => 'required',
            'signers.*.elements.*.page_index' => 'required|integer',
            'signers.*.elements.*.page_width' => 'required|numeric',
            'signers.*.elements.*.page_height' => 'required|numeric',
            'signers.*.elements.*.left' => 'required|numeric',
            'signers.*.elements.*.top' => 'required|numeric',
            'signers.*.elements.*.eleType' => 'required',
            'signers.*.elements.*.width' => 'required|numeric',
            'signers.*.elements.*.height' => 'required|numeric',
            'signers.*.elements.*.is_required' => 'sometimes',
            'signers.*.elements.*.is_deleted' => 'sometimes',
        ];

        return array_merge([
            'document_id' => [
                'required',
                Rule::exists('e_documents', 'id'),
            ],
            'mode' => [
                'required',
                Rule::in([
                    'save', 'send',
                ]),
            ],
            'notification_sequence' => [
                'sometimes',
                new Enum(NotificationSequence::class),
            ],
            'status' => [
                'sometimes',
                new Enum(DocumentStatus::class),
            ],
            'title' => [
                'nullable',
            ],
        ], $additionalRules);
    }
}
