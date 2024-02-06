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
                            {{ __('esign::label.'.$signingValue) }}
                        </span>
                        @php($readValue = $signer->read_status->value)
                        <span
                            class="btn btn-sm btn_{{ $readValue }} me-2 border"
                        >
                            {{ __('esign::label.'.$readValue) }}
                        </span>
                        @php($sendValue = $signer->send_status->value)
                        <span
                            class="btn btn-sm btn_{{ $sendValue }} me-2 border"
                        >
                            {{ __('esign::label.'.$sendValue) }}
                        </span>
                    </div>
                    <span class="text-lg break-all flex items-center">
                        {{ $signer->email ?? $signer->label }}
                    </span>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    @if ($isInProgress && $isSync && $signer->is_next_receiver)
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary me-2"
                        >
                            <i class="fas fa-paper-place"></i>
                            {{ __('esign::label.resend_mail') }}
                        </button>
                    @endif

                    <button
                        type="button"
                        onclick="copyToClipboard('{{ route('esign.signing.index', ['signing_url' => $signer->url]) }}', '{{ __('esign::label.link') }}')"
                        class="btn btn-sm btn-outline-secondary me-2"
                    >
                        <i class="fas fa-link"></i>
                        {{ __('esign::label.copy_link') }}
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary me-2"
                    >
                        <i class="fas fa-eye"></i>
                        {{ __('esign::label.view') }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger me-2"
                    >
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
