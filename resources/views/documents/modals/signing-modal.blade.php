<x-esign::modal title="" id="signing" role="signingModal" backdrop="static">
    <x-slot name="body">
        <nav class="nav nav-tabs" id="nav-tab" role="tablist">
            <button
                class="nav-link ps-2 ps-md-3 pe-2 pe-md-3 active"
                id="nav-draw-tab"
                data-bs-toggle="tab"
                data-bs-target="#nav-draw"
                type="button"
                role="tab"
                aria-controls="nav-draw"
                aria-selected="true"
            >
                <i class="fa fa-pen-nib"></i>
                {{ __('esign::label.draw_freehand') }}
            </button>
            <button
                class="nav-link ps-2 ps-md-3 pe-2 pe-md-3"
                id="nav-type-tab"
                data-bs-toggle="tab"
                data-bs-target="#nav-type"
                type="button"
                role="tab"
                aria-controls="nav-type"
                aria-selected="false"
            >
                <i class="fa fa-font"></i>
                {{ __('esign::label.enter_text') }}
            </button>
        </nav>
        <div class="tab-content mt-3" id="nav-svg-add">
            <div
                class="tab-pane fade active show"
                id="nav-draw"
                role="tabpanel"
                aria-labelledby="nav-draw-tab"
            >
                <small
                    id="signature-pad-reset"
                    class="text-muted opacity-75 position-absolute"
                    style="right: 25px; bottom: 25px; cursor: pointer"
                    title="{{ __('esign::label.clear_signature') }}"
                >
                    <i class="fa-solid fa-trash"></i>
                </small>
                <canvas
                    id="signature-pad"
                    class="border bg-light"
                    width="462"
                    height="200"
                ></canvas>
            </div>
            <div
                class="tab-pane fade"
                id="nav-type"
                role="tabpanel"
                aria-labelledby="nav-type-tab"
            >
                <p>
                    <input
                        type="text"
                        id="typedSignature"
                        class="form-control form-control-lg"
                        placeholder="{{ __('esign::label.my_signature') }}"
                    />
                </p>
            </div>
        </div>
    </x-slot>

    <x-slot name="footer">
        <button
            type="button"
            class="btn btn-outline-secondary bootbox-cancel"
            data-bs-dismiss="modal"
        >
            {{ __('esign::label.close') }}
        </button>
        <button type="button" class="btn btn-primary" id="addSigningBtn">
            <i class="fa fa-check-circle"></i>
            {{ __('esign::label.create') }}
        </button>
    </x-slot>
</x-esign::modal>

@pushonce('js')
    <script src="{{ url('vendor/esign/js/signature_pad.umd.min.js') }}?4.1.7"></script>
    <script>
        const signingModal = $('#signing_modal');
        let signaturePad = null;
        let signingObj = null;

        const createSignaturePad = function () {
            signaturePad = new SignaturePad(
                document.getElementById('signature-pad'),
                {
                    penColor: 'rgb(0, 0, 0)',
                    minWidth: 1,
                    maxWidth: 2,
                },
            );
        };

        $(() => {
            $(document)
                .on('click', '#signature-pad-reset', function (e) {
                    signaturePad.clear();
                    e.preventDefault();
                })
                .on('signing-modal:clear:signature-pad', () => {
                    $('#signature-pad-reset').trigger('click');
                })
                .on('signing-modal:show', function (e, data) {
                    if (!data.type) {
                        return;
                    }

                    signingObj = data.obj;

                    signingModal.attr('data-type', data.type).modal('show');
                })
                .on('signing-modal:hide', () => {
                    signingModal.modal('hide');
                })
                .on('hidden.bs.modal', '#signing_modal', () => {
                    signingObj = null;
                    $(document).trigger('signing-modal:clear:signature-pad');
                    signingModal.removeAttr('data-type');
                    signingModal.find('input#typedSignature').val('');
                    signingModal
                        .find('button.nav-link')
                        .removeAttr('disabled')
                        .prop('disabled', false);
                    signingModal.find('button.nav-link:first').trigger('click');
                })
                .on('click', '#addSigningBtn', (e) => {
                    const type = signingModal.data('type');

                    if (blank(type) || blank(signingObj)) {
                        toast('error', 'Something went wrong!');

                        return;
                    }

                    if (type === 'signature_pad') {
                        $(document).trigger('pad-to-fabric', {
                            type: type,
                            obj: signingObj,
                            svg: signaturePad.toSVG(),
                        });
                    }

                    $(document).trigger('signing-modal:hide');
                });

            createSignaturePad();
        });
    </script>
@endpushonce
