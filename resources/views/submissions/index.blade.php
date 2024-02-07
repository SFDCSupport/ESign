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
                                :redirectUrl="route('esign.documents.show', $document)"
                                icon="edit"
                                class="btn-sm btn-outline-secondary"
                            />
                        </div>
                    </div>

                    @if ($document->signers->count() > 0)
                        @php($isSync = ($document->notificationSequenceIs(\NIIT\ESign\Enum\NotificationSequence::SYNC)))
                        @php($isInProgress = ($document->statusIs(\NIIT\ESign\Enum\DocumentStatus::IN_PROGRESS)))
                        <div class="col-sm-12">
                            <div
                                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-2 mb-2 align-items-center"
                            >
                                <h4 class="h4 mb-0">
                                    {{ __('esign::label.submissions') }}
                                </h4>
                                <div class="btn-toolbar mb-2 mb-md-0">
                                    <div class="btn-group me-2">
                                        @if ($isInProgress && ! $isSync)
                                            <form
                                                class="sendMailForm"
                                                action="{{ route('esign.documents.sendMail', $document) }}"
                                            >
                                                @csrf
                                                <input
                                                    type="hidden"
                                                    name="mode"
                                                    value="all"
                                                />
                                                <x-esign::partials.button
                                                    :value="__('esign::label.resend_mail')"
                                                    class="btn-secondary text-white"
                                                    icon="paper-plane"
                                                    onclick="$(this).closest('form').trigger('submit');"
                                                />
                                            </form>
                                        @endif

                                        <x-esign::partials.button
                                            :value="__('esign::label.export')"
                                            class="btn-secondary text-white"
                                            icon="download"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach ($document->signers as $signer)
                            @include('esign::submissions.partials.submission', compact('signer', 'isInProgress', 'isSync'))
                        @endforeach
                    @endif
                </div>
            </main>
        </div>
    </div>

    @if ($document->signers->count() <= 0)
        <section class="container">
            <div class="send-recipients-sec mt-0">
                <div class="text-center">
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

    @include('esign::documents.modals.signers-send', compact('document'))

    @pushonce('js')
        <script>
            $(() => {
                $(document).on('submit', '.sendMailForm', function (e) {
                    e.preventDefault();

                    const form = $(this);

                    $(document).trigger('loader:show');

                    $.post(form.attr('action'), $(this).serialize())
                        .done((r) => {
                            form.find('button').prop(
                                'disabled',
                                r.status === 1,
                            );
                        })
                        .fail((x) => {
                            toast('error', x.responseText);
                        })
                        .complete(() => $(document).trigger('loader:hide'));
                });
            });
        </script>
    @endpushonce
</x-esign::layout>
