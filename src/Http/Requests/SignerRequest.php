<?php

namespace NIIT\ESign\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
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

        if ($mode === 'create') {
            $additionalRules = [
                'signers.*.id' => 'sometimes|uuid',
                'signers.*.uuid' => 'required',
                'signers.*.label' => 'required',
                'signers.*.email' => 'nullable|email',
                'signers.*.is_deleted' => 'sometimes',
                'signers.*.position' => 'required|integer',
                'signers.*.elements.*.id' => 'sometimes|uuid',
                'signers.*.elements.*.uuid' => 'required',
                'signers.*.elements.*.label' => 'required',
                'signers.*.elements.*.page_index' => 'required|integer',
                'signers.*.elements.*.page_width' => 'required|integer',
                'signers.*.elements.*.page_height' => 'required|integer',
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

        return array_merge([
            'document_id' => [
                'required',
                Rule::exists('e_documents', 'id'),
            ],
            'mode' => [
                'required',
                Rule::in([
                    'create', 'update',
                ]),
            ],
            'notification_sequence' => [
                'sometimes',
                new Enum(NotificationSequence::class),
            ],
            'title' => [
                'nullable',
            ],
        ], $additionalRules);
    }
}
