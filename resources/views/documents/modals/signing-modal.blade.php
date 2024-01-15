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
                <label>
                    <input
                        type="text"
                        class="form-control form-control-lg"
                        placeholder="{{ __('esign::label.my_signature') }}"
                    />
                </label>
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
        <button type="button" class="btn btn-primary">
            <i class="fa fa-check-circle"></i>
            {{ __('esign::label.create') }}
        </button>
    </x-slot>
</x-esign::modal>

@pushonce('js')
    <script src="{{ url('vendor/esign/js/signature_pad.umd.min.js') }}?3.0.0-beta.3"></script>
    <script>
        let signaturePad = null;
        const createSignaturePad = function () {
            signaturePad = new SignaturePad(
                document.getElementById('signature-pad'),
                {
                    penColor: 'rgb(0, 0, 0)',
                    minWidth: 1,
                    maxWidth: 2,
                    onEnd: function () {
                        const file = new File(
                            [dataURLtoBlob(signaturePad.toDataURL())],
                            'draw.png',
                            {
                                type: 'image/png',
                            },
                        );
                        let data = new FormData();
                        data.append('file', file);
                    },
                },
            );
        };

        $(() => {
            $(document).on('click', '#signature-pad-reset', function (e) {
                signaturePad.clear();
                e.preventDefault();
            });

            createSignaturePad();
        });
    </script>
@endpushonce
