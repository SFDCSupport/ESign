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
                            onclick="draftBtnAction()"
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
            const draftBtnAction = () => {
                canvasEditions.forEach((cE) => {
                    cE.getObjects().forEach((obj) => {
                        console.log(obj);
                    });
                });

                $(document).trigger('loader:show');
            };

            const saveBtnAction = () => {
                canvasEditions.forEach((canvasEdition, pageIndex) => {
                    canvasEdition.forEachObject((obj) => {
                        let additionalInfo = {};

                        if (
                            obj instanceof fabric.Text ||
                            obj instanceof fabric.IText
                        ) {
                            additionalInfo = {
                                data: obj.text || obj.getText(),
                            };
                        }

                        if (obj instanceof fabric.Image) {
                            const objBackgroundColor = obj.backgroundColor;

                            obj.backgroundColor = 'rgba(0,0,0,0)';

                            additionalInfo = {
                                data: obj.toDataURL({
                                    format: 'png',
                                    multiplier: 1,
                                }),
                            };

                            obj.backgroundColor = objBackgroundColor;
                        }

                        console.log('Object Info:', {
                            ...additionalInfo,
                            on_page: canvasEdition.page_index + 1,
                            eleType: obj.eleType,
                            left: obj.left,
                            top: obj.top,
                            scale_x: obj.scale_x,
                            scale_y: obj.scale_y,
                            width: obj.width,
                            height: obj.height,
                        });
                    });
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
