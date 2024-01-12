<x-esign::layout :title="__('esign::label.submissions')">
    <div class="container-fluid bg-white">
        <div class="row">
            <main class="col-md-12 ms-sm-auto col-lg-12 pb-0 mb-0">
                <div class="container">
                    <div
                        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-0"
                    >
                        <h4 class="h4 mb-0">{{ $document->title }}</h4>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <x-esign::partials.button
                                    :value="__('esign::label.copy_link')"
                                    icon="link"
                                    class="btn-sm btn-outline-secondary"
                                />
                            </div>
                            <x-esign::partials.button
                                :value="__('esign::label.archive')"
                                icon="archive"
                                class="btn-sm btn-outline-secondary"
                            />
                            <x-esign::partials.button
                                :value="__('esign::label.clone')"
                                :redirectUrl="route('esign.documents.copy', $document)"
                                icon="copy"
                                class="btn-sm btn-outline-secondary"
                            />
                            <x-esign::partials.button
                                :value="__('esign::label.edit')"
                                :redirectUrl="route('esign.documents.edit', $document)"
                                icon="edit"
                                class="btn-sm btn-outline-secondary"
                            />
                        </div>
                    </div>

                    @if ($document->signers->count() > 0)
                        <div class="col-sm-12">
                            <div
                                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-2 mb-2 align-items-center"
                            >
                                <h4 class="h4 mb-0">
                                    {{ __('esign::label.submissions') }}
                                </h4>
                                <div class="btn-toolbar mb-2 mb-md-0">
                                    <div class="btn-group me-2">
                                        <x-esign::partials.button
                                            :value="__('esign::label.export')"
                                            icon="download"
                                        />
                                    </div>
                                    <x-esign::partials.button
                                        data-bs-toggle="modal"
                                        data-bs-target="#sendRecipientModal"
                                        :value="__('esign::label.add_recipient')"
                                        class="btn-secondary text-white"
                                        icon="plus"
                                    />
                                </div>
                            </div>
                        </div>

                        @each('esign::submissions.partials.submission', $document->signers, 'signer')
                    @endif
                </div>
            </main>
        </div>
    </div>

    @if ($document->signers->count() <= 0)
        <section class="grey-bg-section border-top">
            <div class="send-recipients-sec mt-5">
                <div class="text-center max-w-lg">
                    {!! __('esign::label.no_submissions_add_recipients') !!}

                    <div class="Submissions-btns-grp">
                        <a
                            href=""
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#sendRecipientModal"
                        >
                            <i class="fa fa-plus"></i>
                            {{ __('esign::label.send_to_recipients') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @include('esign::documents.modals.send-mail-to-recipient', compact('document'))
</x-esign::layout>
