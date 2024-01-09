<?php

namespace NIIT\ESign\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update_template');
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
        ];

        $mode = $this->request->get('mode');

        if ($mode === 'create') {

        }

        if ($mode === 'update') {

        }

        if ($mode === 'bulkDestroy') {
            return [
                'ids' => 'required|array',
                'ids.*' => Rule::exists('e_templates', 'id'),
            ];
        }

        return $defaultRules;
    }
}
