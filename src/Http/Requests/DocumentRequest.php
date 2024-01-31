<?php

namespace NIIT\ESign\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use NIIT\ESign\Enum\DocumentStatus;
use NIIT\ESign\Enum\NotificationSequence;

class DocumentRequest extends FormRequest
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

        if ($mode === 'bulkDestroy') {
            $additionalRules = ['ids' => 'required|array',
                'ids.*' => Rule::exists('e_documents', 'id'),
            ];
        }

        return array_merge([
            'mode' => [
                'required',
                Rule::in([
                    'create', 'update', 'bulkDestroy',
                ]),
            ],
            'title' => 'required|string|min:3',
            'notification_sequence' => [
                'sometimes',
                new Enum(NotificationSequence::class),
            ],
            'status' => [
                'sometimes',
                new Enum(DocumentStatus::class),
            ],
        ], $additionalRules);
    }
}
