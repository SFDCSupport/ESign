<div class="col-md-12 col-sm-12 mb-3">
    <div class="card document-template-cards submission_card">
        <div class="card-body p-1">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-2 mb-2"
            >
                <div class="btn-toolbar align-items-center">
                    <div class="d-flex flex-column gap-1">
                        @php($signingValue = $signer->signing_status->value)
                        <span
                            class="btn btn-sm btn_{{ $signingValue }} me-2 border"
                        >
                            <em class="fa-solid fa-signature"></em>
                            {{ __('esign::label.'.$signingValue) }}
                        </span>
                        @php($readValue = $signer->read_status->value)
                        <span
                            class="btn btn-sm btn_{{ $readValue }} me-2 border"
                        >
                            <em class="fa-solid fa-envelope-open"></em>
                            {{ __('esign::label.'.$readValue) }}
                        </span>
                        @php($sendValue = $signer->send_status->value)
                        <span
                            class="btn btn-sm btn_{{ $sendValue }} me-2 border"
                        >
                            <em class="fa-solid fa-envelope"></em>
                            {{ __('esign::label.'.$sendValue) }}
                        </span>
                    </div>
                    <span class="text-lg break-all flex items-center">
                        {{ $signer->email ?? $signer->label }}
                    </span>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    @if ($isInProgress && $isSync && $signer->sendStatusIsNot(\NIIT\ESign\Enum\SendStatus::SENT) && $signer->is_next_receiver)
                        <form
                            class="sendMailForm"
                            action="{{ route('esign.documents.sendMail', [$document, $signer]) }}"
                        >
                            @csrf
                            <input type="hidden" name="mode" value="single" />
                            <x-esign::partials.button
                                :value="__('esign::label.resend_mail')"
                                class="btn-sm btn-outline-secondary"
                                icon="paper-plane"
                                onclick="$(this).closest('form').trigger('submit');"
                            />
                        </form>
                    @endif

                    @if ($signer->signingStatusIs(\NIIT\ESign\Enum\SigningStatus::NOT_SIGNED))
                        <x-esign::partials.button
                            icon="link"
                            class="btn-sm btn-outline-secondary"
                            onclick="copyToClipboard('{{ $signer->signingUrl() }}', '{{ __('esign::label.link') }}')"
                            :value="__('esign::label.copy_link')"
                        />
                    @elseif ($signer->signingStatusIs(\NIIT\ESign\Enum\SigningStatus::SIGNED))
                        <x-esign::partials.button
                            icon="eye"
                            class="btn-sm btn-outline-secondary"
                            :value="__('esign::label.view')"
                            :redirectUrl="
                                route('esign.signing.show', [
                                    'signing_url' => $signer->url,
                                ]).'?v'
                            "
                        />
                    @endif

                    <x-esign::partials.button
                        disabled
                        icon="trash"
                        class="d-none btn-sm btn-outline-danger"
                    />
                </div>
            </div>
        </div>
    </div>
</div>
