<x-esign::layout-app :title="__('esign::label.submissions')">
    <section class="grey-bg-section border-top">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div
                        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-2 mb-2 border-bottom"
                    >
                        <h1 class="h2">{{ $document->title }}</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <x-esign::partials.button :value="__('esign::label.copy_link')" icon="link" />
                            </div>

                            <x-esign::partials.button :value="__('esign::label.archive')" icon="archive" />
                            <x-esign::partials.button :value="__('esign::label.clone')" icon="copy" />
                            <x-esign::partials.button :value="__('esign::label.edit')" icon="edit" />
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div
                        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-2 mb-2 align-items-center"
                    >
                        <h4 class="h4 mb-0">{{ __('esign::label.submissions') }}</h4>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <x-esign::partials.button :value="__('esign::label.export')" icon="download" />
                            </div>
                            <x-esign::partials.button data-bs-toggle="modal"
                                                      data-bs-target="#sendRecipientModal"
                                                      :value="__('esign::label.add_recipient')"
                                                      class="btn-secondary text-white" icon="plus" />
                        </div>
                    </div>
                </div>

                @each('esign::submissions.partials.submission', $document->signers, 'signer')
            </div>
        </div>
    </section>

    @include('esign::documents.modals.send-mail-to-recipient', compact('document'))
</x-esign::layout-app>
