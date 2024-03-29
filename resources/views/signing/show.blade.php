<x-esign::layout
    :title="$document->title"
    :document="$document"
    :signer="$signer"
    :isSigningRoute="true"
>
    @pushonce('footJs')
        <script src="{{ url('vendor/esign/js/script.js') }}"></script>
    @endpushonce

    <section class="header-bottom-section">
        <div class="container-fluid">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-0"
            >
                <h4 class="h4">{{ $document->title }}</h4>
                <div>
                    <x-esign::partials.button
                        :value="__('esign::label.download')"
                        icon="download"
                        class="btn-sm btn-outline-secondary"
                        data-bs-toggle="modal"
                        data-bs-target="#signing_success_modal"
                    />
                    <x-esign::partials.button
                        :value="__('esign::label.audit_log')"
                        icon="clipboard-list"
                        class="btn-sm btn-outline-secondary"
                        data-bs-toggle="modal"
                        data-bs-target="#audit_log_modal"
                    />
                </div>
            </div>
        </div>
    </section>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-2 dark-grey-bg">
                <div class="add-doc-sec">
                    <div class="text-center mb-2">
                        <p class="mb-0" style="font-size: 10px">
                            {{ $document->title }}
                        </p>
                    </div>
                    <div class="edit-docs-file">
                        <div id="previewViewer"></div>
                    </div>
                </div>
            </div>

            <main class="col-10">
                <div id="pdfViewer" data-url="{{ $signedDocumentUrl }}"></div>
            </main>
        </div>
    </div>

    @include('esign::partials.renderer')
    @include('esign::partials.audit-log-modal', compact('document'))

    <x-esign::modal id="signing_success">
        @include('esign::signing.partials.success', compact('signedDocumentUrl'))
        <x-slot name="footer">
            <div class="w-100 d-flex justify-content-center">
                <x-esign::partials.button
                    class="btn-dark"
                    :value="__('esign::label.close')"
                    data-bs-dismiss="modal"
                />
            </div>
        </x-slot>
    </x-esign::modal>

    @pushonce('js')
        <script>
            $(() => {
                if (!('v' in getQuery())) {
                    $(
                        'button[data-bs-target="#signing_success_modal"]',
                    ).trigger('click');
                }
            });
        </script>
    @endpushonce
</x-esign::layout>
