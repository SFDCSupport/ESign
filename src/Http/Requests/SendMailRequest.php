<?php

namespace NIIT\ESign\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use NIIT\ESign\Enum\NotificationSequence;
use NIIT\ESign\Models\Document;

class SendMailRequest extends FormRequest
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
        return [
            'mode' => [
                'required',
                Rule::in([
                    'all', 'single',
                ]),
                function (string $attr, mixed $val, \Closure $fail) {
                    /** @var Document $document */
                    $document = $this->route('document');

                    if ($val === 'all' && $this->route('signer')) {
                        $fail(__('esign::validations.document_in_async_sequence_with_signer'));
                    }

                    if ($document->status === NotificationSequence::SYNC && $val === 'all') {
                        $fail(__('esign::validations.document_in_sync_sequence'));
                    }

                    if ($document->status === NotificationSequence::ASYNC && $val === 'single') {
                        $fail(__('esign::validations.document_in_async_sequence'));
                    }
                },
            ],
        ];
    }
}
