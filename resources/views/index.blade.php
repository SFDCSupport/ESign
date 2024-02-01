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
                    <div class="d-none form_container" id="signingContainer">
                        <button
                            type="button"
                            class="btn btn-light minimize_form_button"
                            id="minimize-form-button"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="tab-content p-3" id="elementPanels"></div>
                        <button
                            type="button"
                            data-action="next"
                            id="nextSigningBtn"
                            class="btn btn-secondary w-100"
                        >
                            {{ __('esign::label.next') }}
                        </button>
                        <nav class="sign-type-nav">
                            <div
                                class="nav nav-tabs mb-3"
                                id="elementTabs"
                                role="tablist"
                            ></div>
                        </nav>
                    </div>
                    <button
                        type="button"
                        class="btn btn-dark expand_form_button expand"
                        id="submitSigningFormBtn"
                    >
                        {{ __('esign::label.submit') }}
                        <i class="fas fa-expand-alt"></i>
                    </button>
                </div>
            </main>
        </div>
    </div>

    @include('esign::partials.renderer')

    @pushonce('js')
        <script src="{{ url('vendor/esign/js/signature_pad.umd.min.js') }}?4.1.7"></script>
        <script>
            const signerData = @json($signer->elements);
            const labels = {
                next: '{{ __('esign::label.next') }}',
                previous: '{{ __('esign::label.previous') }}',
                submit: '{{ __('esign::label.submit') }}',
            };
            let signaturePad = null;

            const getSigningElementByType = (id, type, label = null) => {
                if (type === 'textarea') {
                    return `<p>
                      <textarea class="form-control signingElement"
                        id="id-${id}-element" rows="3" data-type="${type}"
                      ></textarea>
                    </p>`;
                }

                if (type === 'signature_pad') {
                    return `<div class="pos_rel digital-sign-pad">
                        <small title="{{ __('esign::label.clear_signature') }}"
                            class="text-muted opacity-75 position-absolute clearSignaturePad"
                            style="right: 25px; bottom: 25px; cursor: pointer">
                            <i class="fa-solid fa-trash"></i>
                        </small>
                        <canvas id="id-${id}-element"
                            class="border bg-light signaturePad signingElement"
                            width="608" height="200" style="display:block;"
                        ></canvas>
                    </div>`;
                }

                return `<p>
                  <input type="text" id="id-${id}-element"
                    class="form-control form-control-lg signingElement"
                    placeholder="${label ?? type}" data-type="${type}"
                  />
                </p>`;
            };

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
                const eles = {
                    signingContainer: $('#signingContainer'),
                    minimizeSigningContainerBtn: $('#minimize-form-button'),
                    maximizeSigningContainerBtn: $(
                        '#submitSigningFormBtn.expand',
                    ),
                    nextBtn: $('#nextSigningBtn'),
                };

                const signerDataCollection = collect(signerData ?? []);

                signerDataCollection.each((element, i) => {
                    const isFirst = i === 0;
                    const id = generateUniqueId('e_');
                    let step = '';

                    if (isFirst) {
                        step = 'first';
                    }

                    if (i + 1 === collect(signerData ?? []).count()) {
                        step = 'last';
                    }

                    $(`<div class="tab-pane fade ${isFirst ? 'active show' : ''}"
                        id="${id}-panel" role="tabpanel"
                        aria-labelledby="${id}-tab"
                        data-object-id="${element.id}"
                        data-element-type="${element.type}"
                        ${step ? 'data-step="' + step + '"' : ''}>
                        <h2>${convertToTitleString(element.label ?? element.type)}</h2>
                        ${getSigningElementByType(id, element.type, element.label)}
                    </div>`).appendTo('#elementPanels');

                    $(`<button class="nav-link ${isFirst ? 'active' : ''} "
                        id="${id}-tab" data-bs-toggle="tab"
                        data-bs-target="#${id}-panel"
                        aria-controls="${id}-panel"
                        aria-selected="${isFirst ? 'true' : 'false'}"
                        type="button" role="tab"
                        ${step ? 'data-step="' + step + '"' : ''}>
                        <span></span>
                    </button>`).appendTo('#elementTabs');
                });

                if (signerDataCollection.count() === 1) {
                    eles.nextBtn
                        .attr('data-action', 'submit')
                        .text(labels.submit);
                }

                $(document)
                    .on(
                        'show.bs.tab',
                        'button[data-bs-toggle="tab"]',
                        function (e) {
                            const currentBtn = $(e.relatedTarget);
                            const nextBtn = $(e.target);
                            const unhighlightId = $(
                                currentBtn.attr('data-bs-target'),
                            ).attr('data-object-id');
                            const highlightId = $(
                                nextBtn.attr('data-bs-target'),
                            ).attr('data-object-id');
                            const [oldObj, oldCanvas] =
                                getObjectById(unhighlightId);
                            const [newObj, newCanvas] =
                                getObjectById(highlightId);
                            const isLast = nextBtn.attr('data-step') === 'last';

                            unhighlightObject(oldObj, oldCanvas);
                            highlightObject(newObj, newCanvas);

                            saveBtnAction('draft');

                            eles.nextBtn
                                .attr('data-action', isLast ? 'submit' : 'next')
                                .text(labels[isLast ? 'submit' : 'next']);
                        },
                    )
                    .on('signers-save', function (e, obj) {
                        $(document).trigger('loader:show');
                    })
                    .on(
                        'click',
                        '#submitSigningFormBtn:not(.expand)',
                        function (e) {
                            e.preventDefault();

                            saveBtnAction();
                            $(document).trigger('signers-save');
                        },
                    )
                    .on('click', '#submitSigningFormBtn.expand', () => {
                        const highlightId = $(
                            $(
                                '#elementTabs button[data-bs-target].active',
                            ).attr('data-bs-target'),
                        ).attr('data-object-id');
                        const [obj, canvas] = getObjectById(highlightId);

                        highlightObject(obj, canvas);
                        eles.signingContainer.removeClass('d-none');
                        $(this).addClass('d-none');
                    })
                    .on(
                        'click',
                        `#${eles.nextBtn.attr('id')}[data-action="next"]`,
                        () => {
                            $('#elementTabs button.nav-link.active')
                                .next('button')
                                .trigger('click');
                        },
                    )
                    .on(
                        'click',
                        `#${eles.nextBtn.attr('id')}[data-action="submit"]`,
                        () => {
                            saveBtnAction();
                        },
                    )
                    .on(
                        'click',
                        `#${eles.minimizeSigningContainerBtn.attr('id')}`,
                        () => {
                            const unhighlightId = $(
                                $(
                                    '#elementTabs button[data-bs-target].active',
                                ).attr('data-bs-target'),
                            ).attr('data-object-id');
                            const [obj, canvas] = getObjectById(unhighlightId);

                            unhighlightObject(obj, canvas);
                            eles.signingContainer.addClass('d-none');
                            $('#expand-form-button').removeClass('d-none');
                        },
                    )
                    .on('click', '.clearSignaturePad', function (e) {
                        signaturePad.clear();
                        e.preventDefault();
                    })
                    .on('signing-modal:clear:signature-pad', () => {
                        $('.clearSignaturePad').trigger('click');
                    })
                    .on('signing-modal:show', function (e, obj) {
                        if (!obj.eleType || !obj.id) {
                            return;
                        }

                        const [oldObj, canvas] = getObjectById(obj.id);

                        highlightObject(oldObj, canvas);

                        eles.maximizeSigningContainerBtn.trigger('click');

                        const tabPane = $(
                            `#elementPanels [data-element-type="${obj.eleType}"][data-object-id="${obj.id}"]`,
                        );

                        if (blank(tabPane)) {
                            return;
                        }

                        if (obj.eleType !== 'signature_pad') {
                            tabPane.find('.signingElement').val(obj.data);
                        }

                        $(
                            `#elementTabs button[data-bs-target="#${tabPane.attr('id')}"]`,
                        ).trigger('click');
                    })
                    .on('signing-modal:hide', () => {
                        eles.minimizeSigningContainerBtn.trigger('click');
                    })
                    .on('fabric-to-pad', function (e, obj) {
                        const [oldObj, canvas] = getObjectById(obj.id);

                        highlightObject(oldObj, canvas);

                        $.when(
                            $(document).trigger(
                                'signing-modal:clear:signature-pad',
                            ),
                        )
                            .then(() => {
                                signaturePad.fromDataURL(obj.data, {
                                    width: 462,
                                    height: 200,
                                });
                            })
                            .then(() => {
                                $(document).trigger('signing-modal:show', obj);
                            });
                    })
                    .on('signing:updated', function (e, obj) {
                        const elementIndex = collect(signerData).search(
                            (e) => e.id === obj.id,
                        );

                        if (elementIndex !== false) {
                            signerData[elementIndex].response = obj.response;
                        }

                        console.log('signing:updated', signerData);
                    })
                    .on('keyup', '.signingElement', function (e) {
                        e.preventDefault();

                        const _t = $(this);

                        $(document).trigger('signing-to-fabric', {
                            eleType: _t.attr('data-type'),
                            id: $('#elementPanels .tab-pane.active').attr(
                                'data-object-id',
                            ),
                            data: _t.val(),
                        });
                    });

                signaturePad = new SignaturePad($('.signaturePad')[0], {
                    penColor: 'rgb(0, 0, 0)',
                    minWidth: 1,
                    maxWidth: 2,
                });

                signaturePad.addEventListener('endStroke', () => {
                    $(document).trigger('signing-to-fabric', {
                        eleType: 'signature_pad',
                        id: $('#elementPanels .tab-pane.active').attr(
                            'data-object-id',
                        ),
                        data: signaturePad.toDataURL(),
                    });
                });
                signaturePad.addEventListener('beginStroke', () => {
                    console.log('beginStroke');
                });
                signaturePad.addEventListener('beforeUpdateStroke', () => {
                    console.log('beforeUpdateStroke');
                });
                signaturePad.addEventListener('afterUpdateStroke', () => {
                    console.log('afterUpdateStroke');
                });
            });
        </script>
    @endpushonce
</x-esign::layout>
