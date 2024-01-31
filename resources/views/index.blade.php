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
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button
                            id="draftBtn"
                            type="button"
                            onclick="saveBtnAction('draft')"
                            class="btn btn-outline-dark"
                        >
                            <i class="fas fa-plane"></i>
                            {{ __('esign::label.draft') }}
                        </button>
                    </div>
                    <button
                        id="saveBtn"
                        type="button"
                        onclick="saveBtnAction()"
                        class="btn btn-primary d-flex align-items-center gap-1"
                    >
                        <i class="fas fa-save"></i>
                        {{ __('esign::label.save') }}
                    </button>
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
                <div
                    id="pdfViewer"
                    data-url="{{ $document->document->url }}"
                ></div>

                <div class="expand-section-bottom">
                    <div class="d-none form_container" id="form-container">
                        <button type="button" class="btn btn-light minimize_form_button" id="minimize-form-button">
                            <i class="fas fa-times"></i>
                        </button>
                        
                       
                        <div class="tab-content p-3" id="nav-tabContent">
                          <div class="tab-pane fade active show" id="digsign-tab" role="tabpanel" aria-labelledby="digsign-tab-tab">
                            <h2>Signature</h2>
                            
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

                            <button type="button" class="btn btn-secondary w-100" id="">
                                <i class="fa fa-check-circle"></i>
                                Next
                            </button>
                          </div>
                          <div class="tab-pane fade" id="signtype-tab" role="tabpanel" aria-labelledby="signtype-tab-tab">
                            <p>
                                <input
                                    type="text"
                                    id="typedSignature"
                                    class="form-control form-control-lg"
                                    placeholder="{{ __('esign::label.my_signature') }}"
                                />
                                
                            </p>
                          </div>

                          <div class="tab-pane fade" id="signtextarea-tab" role="tabpanel" aria-labelledby="signtextarea-tab-tab">
                            <p>
                                <textarea class="form-control" id="signtextarea1" rows="3"></textarea>
                            </p>
                          </div>
                        </div>
                  
                        <nav class="sign-type-nav">
                          <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
                            <button class="nav-link active" id="digsign-tab-tab" data-bs-toggle="tab" data-bs-target="#digsign-tab" type="button" role="tab" aria-controls="digsign-tab" aria-selected="true"><span></span></button>
                            <button class="nav-link" id="signtype-tab-tab" data-bs-toggle="tab" data-bs-target="#signtype-tab" type="button" role="tab" aria-controls="signtype-tab" aria-selected="false"><span></span></button>
                            <button class="nav-link" id="signtextarea-tab-tab" data-bs-toggle="tab" data-bs-target="#signtextarea-tab" type="button" role="tab" aria-controls="signtextarea-tab" aria-selected="false"><span></span></button>
                          </div>
                        </nav>
                  
                    </div>
                    <button type="button" class="btn btn-dark expand_form_button" id="expand-form-button">
                        Submit Form
                        <i class="fas fa-expand-alt"></i>
                    </button>
                  </div>  

            </main>
        </div>
    </div>

    @include('esign::partials.renderer')

    @pushonce('js')
        <script>
            const saveBtnAction = (status = 'save') => {
                if (undefined === canvasEditions) {
                    toast('error', 'Something went wrong!');

                    return;
                }

                let formData = new FormData();

                $(document).trigger('loader:show');

                canvasEditions.forEach((canvasEdition) => {
                    canvasEdition.forEachObject((obj, index) => {
                        if (
                            obj instanceof fabric.Text ||
                            obj instanceof fabric.IText
                        ) {
                            formData.append(
                                `element[${index}][data]`,
                                obj.text || obj.getText(),
                            );
                        }

                        if (obj instanceof fabric.Image) {
                            const objBackgroundColor = obj.backgroundColor;

                            obj.backgroundColor = 'rgba(0,0,0,0)';

                            formData.append(
                                `element[${index}][data]`,
                                dataURLtoBlob(
                                    obj.toDataURL({
                                        format: 'png',
                                        multiplier: 1,
                                    }),
                                ),
                            );

                            obj.backgroundColor = objBackgroundColor;
                        }

                        formData.append(`element[${index}][id]`, obj.id);
                        formData.append(`element[${index}][top]`, obj.top);
                        formData.append(`element[${index}][left]`, obj.left);
                        formData.append(`element[${index}][type]`, obj.eleType);
                        formData.append(
                            `element[${index}][on_page]`,
                            obj.on_page,
                        );
                        formData.append(
                            `element[${index}][scale_x]`,
                            obj.scaleX,
                        );
                        formData.append(
                            `element[${index}][scale_y]`,
                            obj.scaleY,
                        );
                        formData.append(
                            `element[${index}][width]`,
                            obj.width * obj.scaleX,
                        );
                        formData.append(
                            `element[${index}][height]`,
                            obj.height * obj.scaleY,
                        );
                        formData.append(
                            `element[${index}][bottom]`,
                            (obj.top + obj.height) * obj.scaleY,
                        );
                    });
                });

                formData.append('mode', status);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('esign.signing.index', ['signing_url' => $signer->url]) }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-ESign': 'your-custom-header-value',
                    },
                    success: (r) => {
                        if (r.status === 1) {
                            location.reload(true);

                            return;
                        }

                        $(document).trigger('loader:hide');
                        toast('error', r.msg ?? 'Something went wrong!');
                    },
                    error: (x) => {
                        $(document).trigger('loader:hide');
                        toast('error', x.responseText);
                    },
                });
            };

            $(() => {
                $(document).on('signers-save', function (e, obj) {
                    $(document).trigger('loader:show');
                });
            });
        </script>
    @endpushonce
</x-esign::layout>
