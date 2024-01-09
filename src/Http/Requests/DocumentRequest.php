<?php

namespace NIIT\ESign\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $defaultRules = [
            'mode' => [
                'required',
                Rule::in([
                    'create', 'update', 'bulkDestroy',
                ]),
            ],
            'title' => 'required|string|min:3',
        ];

        $mode = $this->request->get('mode');

        if ($mode === 'bulkDestroy') {
            return [
                'ids' => 'required|array',
                'ids.*' => Rule::exists('e_documents', 'id'),
            ];
        }

        return $defaultRules;
    }
}
