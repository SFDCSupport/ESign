<div class="col-md-12 col-sm-12 mb-3">
    <div class="card document-template-cards">
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
                    @if ($isInProgress && $isSync && $signer->is_next_receiver)
                        <x-esign::partials.button
                            icon="paper-plane"
                            class="btn-sm btn-outline-secondary"
                            :value="__('esign::label.resend_mail')"
                        />
                    @endif

                    <x-esign::partials.button
                        icon="link"
                        class="btn-sm btn-outline-secondary"
                        onclick="copyToClipboard('{{ route('esign.signing.index', ['signing_url' => $signer->url]) }}', '{{ __('esign::label.link') }}')"
                        :value="__('esign::label.copy_link')"
                    />
                    <x-esign::partials.button
                        icon="eye"
                        class="btn-sm btn-outline-secondary"
                        :value="__('esign::label.view')"
                        :redirect="
                            route('esign.documents.submissions.show', [
                                'document' => $document,
                                'submission' => $signer->submissions,
                            ])
                        "
                    />
                    <x-esign::partials.button
                        disabled
                        icon="trash"
                        class="btn-sm btn-outline-danger"
                    />
                </div>
            </div>
        </div>
    </div>
</div>
