<script>
    let loadedData = [];

    const getActiveSigner = (uuid = null, label = null) => {
        const _ele = $('span.selectedSigner[data-active-signer]');

        if (uuid) {
            _ele.attr('data-active-signer', uuid);
        }

        if (label) {
            _ele.html(label);
        }

        return uuid ?? _ele.attr('data-active-signer');
    };
    const generateUniqueId = (() => {
        let counter = 0;

        return (prefix = '') => {
            const timestamp = new Date().getTime();
            return `${prefix}${timestamp}-${counter++}`;
        };
    })();
    const convertToTitleString = (string) =>
        string
            .replace(/_/g, ' ')
            .replace(/^\s*/, '')
            .replace(/([a-z])([A-Z])/g, '$1 $2')
            .replace(/(?:^|(?<=\s))\S/g, (char) => char.toUpperCase());
    const getCSRFToken = () =>
        $('meta[name="csrf-token"]').attr('content') || null;
    const getDocumentId = () =>
        $('meta[name="document-id"]').attr('content') || null;
    const getSignerId = () =>
        $('meta[name="signer-id"]').attr('content') || null;
    const dataURLtoBlob = (dataurl) => {
        let arr = dataurl.split(','),
            mime = arr[0].match(/:(.*?);/)[1],
            bstr = atob(arr[1]),
            n = bstr.length,
            u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], { type: mime });
    };
    const svgToDataUrl = (svg) => 'data:image/svg+xml;base64,' + btoa(svg);
    const trimSvgWhitespace = (svgContent) => {
        if (!svgContent) {
            return null;
        }

        let svgContainer = document.createElement('div');
        svgContainer.classList.add('invisible');
        svgContainer.classList.add('position-absolute');
        svgContainer.classList.add('top-0');
        svgContainer.classList.add('start-0');
        svgContainer.style = 'z-index: -1;';
        svgContainer.innerHTML = svgContent;

        document.body.appendChild(svgContainer);

        let svg = svgContainer.querySelector('svg');
        let box = svg.getBBox();

        svg.setAttribute(
            'viewBox',
            [box.x, box.y, box.width, box.height].join(' '),
        );

        document.body.removeChild(svgContainer);

        return svgContainer.innerHTML;
    };
    const copyToClipboard = (text, type) => {
        const getMsg = (mode) =>
            (mode === 'success'
                ? '{{ __('esign::label.copied_to_clipboard') }}'
                : '{{ __('esign::label.unable_to_copy') }}'
            ).replace(/:TYPE:/gi, type);

        const _success = () => toast('success', getMsg('success', type));
        const _error = (err) => {
            const _msg = getMsg('error', type);

            toast('error', _msg);
            console.error(_msg, err);
        };

        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(_success).catch(_error);
        } else {
            const textarea = $('<textarea>')
                .val(text)
                .appendTo('body')
                .select();

            try {
                document.execCommand('copy');
                _success();
            } catch (err) {
                _error(err);
            } finally {
                textarea.remove();
            }
        }
    };
    const ordinal = (number) => {
        let suffix;

        if (number % 100 >= 11 && number % 100 <= 13) {
            suffix = '{{ __('esign::label.th') }}';
        } else {
            switch (number % 10) {
                case 1:
                    suffix = '{{ __('esign::label.st') }}';
                    break;
                case 2:
                    suffix = '{{ __('esign::label.nd') }}';
                    break;
                case 3:
                    suffix = '{{ __('esign::label.rd') }}';
                    break;
                default:
                    suffix = '{{ __('esign::label.th') }}';
                    break;
            }
        }

        return number + suffix;
    };

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
        },
    });

    $.fn.highestData = function (data = 'signer') {
        let highest = -Infinity;

        this.each(function () {
            const value = parseInt($(this).attr('data-' + data), 10);

            if (!isNaN(value)) {
                highest = Math.max(highest, value);
            }
        });

        return isFinite(highest) ? highest : null;
    };

    $(document)
        .on('signer:added', function (e, obj) {
            if (
                obj.from === 'loadedData' ||
                collect(loadedData.signers).firstWhere('uuid', obj.uuid)
            ) {
                return;
            }

            collect(loadedData.signers).push({
                uuid: obj.uuid,
                text: obj.text,
                position: obj.signer_index,
                elements: [],
            });

            console.log('signer:added', loadedData);
        })
        .on('signer:updated', function (e, obj) {
            if (obj.from === 'loadedData') {
                return;
            }

            const index = collect(loadedData.signers).search(
                (i) => i.uuid === obj.uuid,
            );

            if (index !== false) {
                loadedData.signers[index] = collect(loadedData.signers[index])
                    .merge({
                        text: obj.text,
                        position: obj.position ?? obj.signer_index,
                        email: obj.email || null,
                    })
                    .all();
            }

            console.log('signer:updated', loadedData);
        })
        .on('signer:removed', function (e, obj) {
            if (obj.from === 'loadedData') {
                return;
            }

            const index = collect(loadedData.signers).search(
                (i) => i.uuid === obj.uuid,
            );

            if (index !== false) {
                loadedData.signers[index] = collect(loadedData.signers[index])
                    .merge({
                        is_deleted: true,
                    })
                    .all();
            }

            console.log('signer:removed', loadedData);
        })
        .on('signer:reordered', function (e, obj) {
            const indexA = collect(loadedData.signers).search(
                (i) => i.uuid === obj.uuid,
            );
            const indexB = collect(loadedData.signers).search(
                (i) => i.uuid === obj.withUuid,
            );

            if (indexA !== false && indexB !== false) {
                const tempPosition = loadedData.signers[indexA].position;

                loadedData.signers[indexA].position =
                    loadedData.signers[indexB].position;
                loadedData.signers[indexB].position = tempPosition;
            }

            console.log('signer:reordered', loadedData);
        })
        .on('signer:element:added', function (e, obj) {
            if (obj.from === 'loadedData') {
                return;
            }

            const signerIndex = collect(loadedData.signers).search(
                (i) => i.uuid === obj.signer_uuid,
            );

            if (signerIndex !== false) {
                loadedData.signers[signerIndex].elements.push({
                    uuid: obj.uuid,
                    page_index: obj.page_index,
                    page_width: obj.page_width,
                    page_height: obj.page_height,
                    eleType: obj.eleType,
                    left: obj.left,
                    top: obj.top,
                    width: obj.width,
                    height: obj.height,
                    text: obj.text,
                    is_required: obj.is_required,
                    signer_uuid: obj.signer_uuid,
                });
            }

            console.log('signer:element:added', obj, loadedData);
        })
        .on('signer:element:updated', function (e, obj) {
            if (obj.from === 'loadedData') {
                return;
            }

            const signerIndex = collect(loadedData.signers).search(
                (i) => i.uuid === obj.signer_uuid,
            );

            if (signerIndex !== false) {
                const elementIndex = collect(
                    loadedData.signers[signerIndex].elements,
                ).search((e) => e.uuid === obj.uuid);

                if (elementIndex !== false) {
                    loadedData.signers[signerIndex].elements[elementIndex] =
                        collect(
                            loadedData.signers[signerIndex].elements[
                                elementIndex
                            ],
                        )
                            .merge(
                                obj.from !== 'sidebar'
                                    ? {
                                          left: obj.left,
                                          top: obj.top,
                                          width: obj.width,
                                          height: obj.height,
                                      }
                                    : {
                                          is_required: obj.is_required ?? true,
                                      },
                            )
                            .when(obj.text, (c) =>
                                c.merge({
                                    text: obj.text,
                                }),
                            )
                            .all();
                }
            }

            console.log('signer:element:updated', loadedData);
        })
        .on('signer:element:removed', function (e, obj) {
            if (obj.from === 'loadedData') {
                return;
            }

            const signerIndex = collect(loadedData.signers).search(
                (i) => i.uuid === obj.signer_uuid,
            );

            if (signerIndex !== false) {
                const elementIndex = collect(
                    loadedData.signers[signerIndex].elements,
                ).search((e) => e.uuid === obj.uuid);

                if (elementIndex !== false) {
                    loadedData.signers[signerIndex].elements[elementIndex] =
                        collect(
                            loadedData.signers[signerIndex].elements[
                                elementIndex
                            ],
                        )
                            .merge({
                                is_deleted: true,
                            })
                            .all();
                }
            }

            console.log('signer:element:removed', loadedData);
        })
        .on('process-ids', function (e, obj) {
            const loadedDataCollection = collect(loadedData.signers);

            collect(obj).each((newData, uuid) => {
                const itemIndex = loadedDataCollection.search(
                    (i) => i.uuid === uuid,
                );

                if (itemIndex !== false) {
                    loadedData.signers[itemIndex].id = newData.id;

                    if (newData.elements) {
                        collect(newData.elements).each(
                            (newElementId, newElementUuid) => {
                                const elementIndex = collect(
                                    loadedData.signers[itemIndex].elements,
                                ).search((i) => i.uuid === newElementUuid);

                                if (elementIndex !== false) {
                                    loadedData.signers[itemIndex].elements[
                                        elementIndex
                                    ].id = newElementId;
                                }
                            },
                        );
                    }
                }
            });

            console.log('process-ids', loadedData);
        })
        .on('document:updated', function (e, obj) {
            if (obj.from === 'loadedData') {
                return;
            }

            delete obj.from;

            collect(obj).each((v, k) => {
                loadedData[k] = v;
            });

            console.log('document:updated', loadedData);
        });
</script>
