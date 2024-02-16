<?php

namespace NIIT\ESign\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SigningRequest extends FormRequest
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
        $mode = $this->request->get('mode');

        return [
            'mode' => [
                'required',
                Rule::in([
                    'save', 'draft',
                ]),
            ],
            'signed_document' => 'required',
            'metaData' => 'nullable',
            'element.*.id' => [
                'required',
                Rule::exists('e_signer_elements', 'id'),
            ],
            'element.*.type' => 'required',
            'element.*.width' => 'required',
            'element.*.height' => 'required',
            'element.*.left' => 'required',
            'element.*.top' => 'required',
            'element.*.page_index' => 'required',
            'element.*.page_width' => 'required',
            'element.*.page_height' => 'required',
            'element.*.data' => 'required_if:mode,save',
        ];
    }
}
