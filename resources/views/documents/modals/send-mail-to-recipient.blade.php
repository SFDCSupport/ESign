<x-esign::modal
    id="send_recipient"
    backdrop="static"
    :title="__('esign::label.add_signers')"
>
    <x-slot name="body">
        <form
            method="POST"
            action="{{ route('esign.documents.signers.store', $document) }}"
        >
            @csrf
            <input
                type="hidden"
                name="documentId"
                value="{{ $document->id }}"
            />
            <div class="dark-bg-card p-2 py-3 rounded mb-3">
                <label for="formControlInput1" class="col-form-label pt-1 pb-1">
                    {{ __('esign::label.nth_signer', ['nth' => ordinal(1)]) }}
                </label>
                <input
                    class="form-control"
                    id="formControlInput1"
                    placeholder="{{ __('esign::label.type_email_here') }}"
                />
            </div>
            <div class="col-sm-12 mt-3 d-flex justify-content-between">
                <div class="col">
                    <div class="form-check mb-2">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            value=""
                            id="flexCheckDefault"
                        />
                        <label class="form-check-label" for="flexCheckDefault">
                            {{ __('esign::label.send_emails') }}
                        </label>
                    </div>
                    
                </div>
                <div class="col text-end">
                    <div class="form-check">
                        <a
                            class="editmessage-link"
                            href="javascript: void(0);"
                            data-toggle="section"
                            data-target="#mailSection"
                        >
                            {{ __('esign::label.edit_message') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div
                    class="dark-bg-card p-2 rounded mb-3 mt-3 d-none"
                    id="mailSection"
                >
                    <div class="row">
                        <div class="col-sm-12 mb-3">
                            <label for="mail_subject" class="form-label mb-1">
                                {{ __('esign::label.subject') }}
                            </label>
                            <input
                                id="mail_subject"
                                class="form-control form-control"
                                type="text"
                                value="You are invited to submit a form"
                                placeholder="{{ __('esign::label.subject') }}"
                            />
                        </div>
                        <div class="col-sm-12 mb-3">
                            <label for="mail_body" class="form-label mb-1">
                                {{ __('esign::label.body') }}
                                <i class="fa fa-info-circle"></i>
                            </label>
                            <textarea
                                class="form-control"
                                id="mail_body"
                                rows="9"
                                placeholder="{{ __('esign::label.body') }}"
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="form-check mb-2">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        value=""
                        id="preserve_order"
                    />
                    <label class="form-check-label" for="preserve_order">
                        {{ __('esign::label.preserve_order') }}
                    </label>
                </div>
            </div>
        </form>
    </x-slot>

    <x-slot name="footer">
        <div class="Submissions-btns-grp w-100">
            <button type="button" class="btn btn-sm btn-dark add-part-btn">
                {{ __('esign::label.send') }}
            </button>
        </div>
    </x-slot>
</x-esign::modal>

<script>
    $(() => {
        $(document).on(
            'shown.bs.modal',
            '#send_recipient_modal',
            function (e) {},
        );
    });
</script>
