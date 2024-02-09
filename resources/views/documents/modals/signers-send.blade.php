<x-esign::modal
    id="signers_send"
    backdrop="static"
    :title="__('esign::label.signers_detail')"
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
            <div class="col-12 signersHolder"></div>
            <div class="col-sm-12 mt-3 d-flex justify-content-between">
                <div class="col">
                    <div class="form-check mb-2">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="notification_sequence"
                            id="preserve_order"
                        />
                        <label class="form-check-label" for="preserve_order">
                            {{ __('esign::label.preserve_order') }}
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
        </form>
    </x-slot>

    <x-slot name="footer">
        <div class="col">
            <x-esign::partials.button
                class="btn-primary w-100"
                id="signersSaveBtn"
                :value="__('esign::label.save')"
            />
        </div>
        <div class="col">
            <x-esign::partials.button
                class="btn-dark w-100"
                id="signersSendBtn"
                :value="__('esign::label.send')"
            />
        </div>
    </x-slot>
</x-esign::modal>

<script>
    const signersSendModal = $('#signers_send_modal');
    const _signerEmailTemplate = `<div class="dark-bg-card p-2 py-3 rounded mb-3 signerEmail"
                                    data-signer-index="__INDEX" data-signer-uuid="__UUID"
                                    data-signer-text="__LABEL">
                                    <label for="formControlInput__INDEX" class="col-form-label pt-1 pb-1">
                                        __LABEL
                                    </label>
                                    <input
                                        data-rule-email="true"
                                        data-rule-required="true"
                                        data-rule-unique-signers-email="true"
                                        name="signer[__UUID][email]"
                                        class="form-control required"
                                        id="formControlInput__INDEX"
                                        value="__EMAIL" type="email"
                                        placeholder="{{ __('esign::label.type_email_here') }}" />
                                  </div>`;

    $(() => {
        const signersForm = signersSendModal.find('form');

        $.validator.addMethod(
            'unique-signers-email',
            function (v, e) {
                let vals = $(e)
                    .closest('div.signersHolder')
                    .find('input[name$="[email]"]')
                    .not(e)
                    .map(function () {
                        return $(this).val();
                    })
                    .get();

                return !vals.includes(v);
            },
            '{{ __('esign::validations.unique_signers_email') }}',
        );

        signersForm.validate({
            debug: false,
        });

        $(document)
            .on('shown.bs.modal', '#signers_send_modal', () => {
                const signersHolderEle =
                    signersSendModal.find('.signersHolder');

                signersHolderEle.html('');
                $(
                    signersSendModal
                        .find('.editmessage-link')
                        .attr('data-target'),
                ).addClass('d-none');

                collect(loadedData?.signers || [])
                    .sortBy('position')
                    .where('is_deleted', '!==', true)
                    .each((s, i) => {
                        signersHolderEle.append(
                            $.trim(_signerEmailTemplate)
                                .replace(/__UUID/gi, s.uuid)
                                .replace(/__LABEL/gi, s.text)
                                .replace(/__EMAIL/gi, s.email ?? '')
                                .replace(/__INDEX/gi, s.position ?? i + 1),
                        );
                    });

                signersForm
                    .find('input#preserve_order')
                    .prop(
                        'checked',
                        loadedData?.notification_sequence === 'sync',
                    );
            })
            .on('click', '#signersSaveBtn,#signersSendBtn', function () {
                const _t = $(this);

                if (!signersForm.valid()) {
                    console.log(signersForm.validate().errorList);
                    toast(
                        'error',
                        '{{ __('esign::validations.required_elements') }}',
                    );
                    return;
                }

                signersForm.find('div[data-signer-uuid]').each(function () {
                    const _t = $(this);

                    $(document).trigger('signer:updated', {
                        from: 'signersModal',
                        uuid: _t.attr('data-signer-uuid'),
                        text: _t.attr('data-signer-text'),
                        email: _t.find('input[name$="[email]"]').val(),
                        position: _t.attr('data-signer-index'),
                    });
                });

                const notification_sequence = signersForm
                    .find('input[name="notification_sequence"]')
                    .is(':checked')
                    ? 'sync'
                    : 'async';

                const obj = {
                    from: 'signersSend',
                    notification_sequence: notification_sequence,
                };

                const isSend = _t.attr('id') === 'signersSendBtn';

                if (isSend) {
                    obj.status = 'in_progress';
                }

                $(document).trigger('document:updated', obj);

                signersSendModal
                    .find('[data-bs-dismiss="modal"]')
                    .trigger('click');

                try {
                    $(document).trigger('signers-save', {
                        type: isSend ? 'send' : 'save',
                    });
                } catch (e) {
                    toast('error', e);
                }
            });
    });
</script>
