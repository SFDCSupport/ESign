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
            'element.*.id' => [
                'required',
                Rule::exists('e_signer_elements', 'id'),
            ],
            'element.*.type' => 'required',
            'element.*.width' => 'required',
            'element.*.height' => 'required',
            'element.*.left' => 'required',
            'element.*.top' => 'required',
            'element.*.scale_x' => 'required',
            'element.*.scale_y' => 'required',
            'element.*.on_page' => 'required',
            'element.*.bottom' => 'required',
            'element.*.data' => 'required_if:mode,save',
        ];
    }
}
