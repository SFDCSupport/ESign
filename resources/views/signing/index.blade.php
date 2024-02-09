<x-esign::layout
    :title="$document->title"
    :document="$document"
    :signer="$signer"
    :isSigningRoute="true"
>
    @pushonce('css')
        <link
            href="{{ url('vendor/esign/css/jquery.datetimepicker.css') }}"
            rel="stylesheet"
        />
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
                        <x-esign::partials.button
                            icon="times"
                            class="btn btn-light minimize_form_button"
                            id="minimize-form-button"
                        />
                        <div class="tab-content p-3" id="elementPanels"></div>
                        <x-esign::partials.button
                            data-action="next"
                            id="nextSigningBtn"
                            class="btn-secondary w-100"
                            :value="__('esign::label.next')"
                        />
                        <nav class="sign-type-nav">
                            <div
                                class="nav nav-tabs mb-3"
                                id="elementTabs"
                                role="tablist"
                            ></div>
                        </nav>
                    </div>
                    <x-esign::partials.button
                        class="btn-dark expand_form_button expand"
                        id="submitSigningFormBtn"
                        icon="expand-alt"
                        :value="__('esign::label.submit')"
                    />
                </div>
            </main>
        </div>
    </div>

    @include('esign::partials.renderer')
    <x-esign::modal
        id="signing_success"
        size="modal-fullscreen"
        backdrop="static"
        data-bs-keyboard="false"
    >
        <div
            class="w-100 h-100 d-flex justify-content-center align-items-center"
        >
            @include('esign::signing.partials.success')
        </div>
    </x-esign::modal>

    @pushonce('footJs')
        <script src="{{ url('vendor/esign/js/jquery.datetimepicker.full.min.js') }}"></script>
        <script src="{{ url('vendor/esign/js/script.js') }}"></script>
    @endpushonce

    @pushonce('js')
        <script src="{{ url('vendor/esign/js/signature_pad.umd.min.js') }}?4.1.7"></script>
        <script>
            const labels = {
                next: '{{ __('esign::label.next') }}',
                previous: '{{ __('esign::label.previous') }}',
                submit: '{{ __('esign::label.submit') }}',
            };
            let signaturePad = null;
            let signerData = [];

            const getSigningElementByType = (
                id,
                type,
                required = true,
                label = null,
            ) => {
                if (type === 'textarea') {
                    return `<p>
                      <textarea class="form-control signingElement"
                        id="id-${id}-element" rows="3" data-type="${type}"
                        ${required ? 'required' : ''}
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
                            ${required ? 'required' : ''}
                        ></canvas>
                    </div>`;
                }

                const $type = type === 'date' ? 'text' : type;

                return `<p>
                  <input type="${$type}" id="id-${id}-element"
                    class="form-control form-control-lg signingElement"
                    placeholder="${label ?? type}" data-type="${type}"
                    ${required ? 'required' : ''}
                  />
                </p>`;
            };

            const startCountdown = (countdownDiv, seconds, msg) => {
                const interval = setInterval(function () {
                    countdownDiv.text(msg.replace(':SECONDS:', seconds));

                    if (seconds <= 0) {
                        clearInterval(interval);
                    } else {
                        seconds--;
                    }
                }, 1000);
            };

            const saveBtnAction = (status = 'save') => {
                if (undefined === canvasEditions) {
                    toast(
                        'error',
                        '{{ __('esign::validations.something_went_wrong') }}',
                    );

                    return;
                }

                let formData = new FormData();

                $(document).trigger('loader:show');

                $.when(
                    canvasEditions.forEach((canvasEdition) => {
                        canvasEdition.forEachObject((obj, index) => {
                            if (
                                obj instanceof fabric.Text ||
                                obj instanceof fabric.IText
                            ) {
                                const element = $(`#id-${obj.uuid}-element`);

                                formData.append(
                                    `element[${index}][data]`,
                                    element.length > 0
                                        ? element.val()
                                        : obj.text || obj.getText(),
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
                            formData.append(
                                `element[${index}][left]`,
                                obj.left,
                            );
                            formData.append(
                                `element[${index}][type]`,
                                obj.eleType,
                            );
                            formData.append(
                                `element[${index}][page_index]`,
                                obj.page_index,
                            );
                            formData.append(
                                `element[${index}][page_width]`,
                                obj.page_width,
                            );
                            formData.append(
                                `element[${index}][page_height]`,
                                obj.page_height,
                            );
                            formData.append(
                                `element[${index}][width]`,
                                obj.width * obj.scaleX,
                            );
                            formData.append(
                                `element[${index}][height]`,
                                obj.height * obj.scaleY,
                            );
                        });
                    }),
                )
                    .then(
                        () =>
                            formData.append('mode', status) &&
                            formData.append('_token', '{{ csrf_token() }}'),
                    )
                    .then(() => console.log(formData))
                    .then(() =>
                        $.ajax({
                            url: '{{ $signer->signingUrl() }}',
                            type: 'POST',
                            data: formData,
                            contentType: false,
                            processData: false,
                            headers: @json(request()->signingHeaders()),
                            success: (r) => {
                                if (r.status === 1) {
                                    const signingSuccessModal = $(
                                        '#signing_success_modal',
                                    );

                                    signingSuccessModal.modal('show');
                                    signingSuccessModal
                                        .find('.downloadBtn')
                                        .attr(
                                            'href',
                                            r.downloadUrl ||
                                                'javascript: void(0);',
                                        );

                                    if (blank(r.downloadUrl)) {
                                        $(document).trigger('loader:show');

                                        if (!blank(r.redirectUrl)) {
                                            $(location).attr(
                                                'href',
                                                r.redirectUrl,
                                            );
                                        } else {
                                            location.reload(true);
                                        }
                                    } else {
                                        const redirectTime = 60;
                                        const metaRedirect = $(
                                            '<meta http-equiv="refresh">',
                                        );

                                        if (!blank(r.redirectUrl)) {
                                            metaRedirect.attr(
                                                'content',
                                                `${redirectTime};url=${r.redirectUrl}`,
                                            );
                                        } else {
                                            metaRedirect.attr(
                                                'content',
                                                redirectTime,
                                            );
                                        }

                                        $('head').append(metaRedirect);

                                        toast(
                                            'info',
                                            `<div id="countdown">Redirecting in ${redirectTime} seconds!</div>`,
                                            false,
                                        );

                                        startCountdown(
                                            $('.toast-body #countdown'),
                                            redirectTime,
                                            '{{ __('esign::label.redirecting_in_seconds') }}',
                                        );
                                    }

                                    return;
                                }

                                toast(
                                    'error',
                                    r.msg ??
                                        '{{ __('esign::validations.something_went_wrong') }}',
                                );
                            },
                            error: (x) => toast('error', x.responseText),
                            complete: () => $(document).trigger('loader:hide'),
                        }),
                    );
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

                $(document).on('elements-added-to-canvas', () => {
                    signerData = collect(loadedData.signers)
                        .pluck('elements')
                        .sortBy('position')
                        .flatten(1);

                    $.when(
                        signerData.each((element, i) => {
                            const isFirst = i === 0;
                            let step = '';

                            if (isFirst) {
                                step = 'first';
                            }

                            if (i + 1 === signerElements.count()) {
                                step = 'last';
                            }

                            $(`<div class="tab-pane fade ${isFirst ? 'active show' : ''}"
                                id="${element.uuid}-panel" role="tabpanel"
                                aria-labelledby="${element.uuid}-tab"
                                data-object-id="${element.id}"
                                data-element-type="${element.type}"
                                ${step ? 'data-step="' + step + '"' : ''}>
                                <h2>${convertToTitleString(element.text ?? element.eleType)}</h2>
                                ${getSigningElementByType(element.uuid, element.eleType, element.is_required, element.text)}
                            </div>`).appendTo('#elementPanels');

                            $(`<button class="nav-link ${isFirst ? 'active' : ''} "
                                id="${element.uuid}-tab" data-bs-toggle="tab"
                                data-bs-target="#${element.uuid}-panel"
                                aria-controls="${element.uuid}-panel"
                                aria-selected="${isFirst ? 'true' : 'false'}"
                                type="button" role="tab"
                                ${step ? 'data-step="' + step + '"' : ''}>
                                <span></span>
                            </button>`).appendTo('#elementTabs');
                        }),
                    ).then(() => {
                        $('input[data-type="date"]').datetimepicker({
                            timepicker: false,
                            format: 'd-M-Y',
                            scrollInput: false,
                            validateOnBlur: false,
                            step: 30,
                        });
                    });

                    if (signerData.count() === 1) {
                        eles.nextBtn
                            .attr('data-action', 'submit')
                            .text(labels.submit);
                    }
                });

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

                            // saveBtnAction('draft');

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

                const signaturePadEle = $('.signaturePad');

                if (signaturePadEle.length > 0) {
                    signaturePad = new SignaturePad(signaturePadEle[0], {
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
                }
            });
        </script>
    @endpushonce
</x-esign::layout>
