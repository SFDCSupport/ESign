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
