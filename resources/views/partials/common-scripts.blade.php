<script>
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
    const getCSRFToken = () =>
        $('meta[name="csrf-token"]').attr('content') || null;
    const getDocumentId = () =>
        $('meta[name="document-id"]').attr('content') || null;
    const getSignerId = () =>
        $('meta[name="signer-id"]').attr('content') || null;
    const blank = (value) => {
        if (Array.isArray(value)) {
            return value.length === 0;
        }

        if (value instanceof Date) {
            return false;
        }

        if (typeof value === 'object' && value !== null) {
            return Object.keys(value).length === 0;
        }

        return ['', null, undefined].includes(value);
    };
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
                obj.from === 'loadedObject' ||
                collect(loadedData).firstWhere('uuid', obj.uuid)
            ) {
                return;
            }

            collect(loadedData).push({
                uuid: obj.uuid,
                label: obj.label,
                position: obj.signer_index,
                elements: [],
            });

            console.log('signer:added', loadedData);
        })
        .on('signer:updated', function (e, obj) {
            if (obj.from === 'loadedObject') {
                return;
            }

            const index = collect(loadedData).search(
                (i) => i.uuid === obj.uuid,
            );

            if (index !== false) {
                loadedData[index] = collect(loadedData[index])
                    .merge({
                        label: obj.label,
                        position: obj.signer_index,
                    })
                    .all();
            }

            console.log('signer:updated', loadedData);
        })
        .on('signer:removed', function (e, obj) {
            if (obj.from === 'loadedObject') {
                return;
            }

            const index = collect(loadedData).search(
                (i) => i.uuid === obj.uuid,
            );

            if (index !== false) {
                loadedData[index] = collect(loadedData[index])
                    .merge({
                        is_deleted: true,
                    })
                    .all();
            }

            console.log('signer:removed', loadedData);
        })
        .on('signer:reordered', function (e, obj) {
            const signerA = collect(loadedData).firstWhere('uuid', obj.uuid);
            const signerB = collect(loadedData).firstWhere(
                'uuid',
                obj.withUuid,
            );

            if (signerA && signerB) {
                const tempPosition = signerA.position;

                signerA.position = signerB.position;
                signerB.position = tempPosition;
            }

            console.log('signer:reordered', loadedData);
        })
        .on('signer:element:added', function (e, obj) {
            if (obj.from === 'loadedObject') {
                return;
            }

            const signerIndex = collect(loadedData).search(
                (i) => i.uuid === obj.signer_uuid,
            );

            if (signerIndex !== false) {
                loadedData[signerIndex].elements.push({
                    uuid: obj.uuid,
                    on_page: obj.on_page,
                    type: obj.eleType,
                    offset_x: obj.left,
                    offset_y: obj.top,
                    width: obj.width,
                    height: obj.height,
                    is_required: obj.is_required,
                    signer_uuid: obj.signer_uuid,
                });
            }

            console.log('signer:element:added', obj, loadedData);
        })
        .on('signer:element:updated', function (e, obj) {
            if (obj.from === 'loadedObject') {
                return;
            }

            const signerIndex = collect(loadedData).search(
                (i) => i.uuid === obj.signer_uuid,
            );

            if (signerIndex !== false) {
                const elementIndex = collect(
                    loadedData[signerIndex].elements,
                ).search((e) => e.uuid === obj.uuid);

                if (elementIndex !== false) {
                    loadedData[signerIndex].elements[elementIndex] = collect(
                        loadedData[signerIndex].elements[elementIndex],
                    )
                        .merge(
                            obj.from !== 'sidebar'
                                ? {
                                      offset_x: obj.left,
                                      offset_y: obj.top,
                                      width: obj.width,
                                      height: obj.height,
                                  }
                                : {
                                      is_required: obj.is_required ?? true,
                                  },
                        )
                        .all();
                }
            }

            console.log('signer:element:updated', loadedData);
        })
        .on('signer:element:removed', function (e, obj) {
            if (obj.from === 'loadedObject') {
                return;
            }

            const signerIndex = collect(loadedData).search(
                (i) => i.uuid === obj.signer_uuid,
            );

            if (signerIndex !== false) {
                const elementIndex = collect(
                    loadedData[signerIndex].elements,
                ).search((e) => e.uuid === obj.uuid);

                if (elementIndex !== false) {
                    loadedData[signerIndex].elements[elementIndex] = collect(
                        loadedData[signerIndex].elements[elementIndex],
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
            const loadedDataCollection = collect(loadedData);

            collect(obj).each((newData, uuid) => {
                const item = loadedDataCollection.firstWhere('uuid', uuid);

                if (item) {
                    item.id = newData.id;

                    if (newData.elements) {
                        collect(newData.elements).each(
                            (newElementId, newElementUuid) => {
                                console.log(newElementId, newElementUuid);
                                const element = collect(
                                    item.elements,
                                ).firstWhere('uuid', newElementUuid);

                                if (element) {
                                    element.id = newElementId;
                                }
                            },
                        );
                    }
                }
            });
        });

    let loadedData = collect(@json($document->signers ?? [['label' => '1st Signer', 'position' => 1, 'elements' => []]]))
        .map((item, i) => {
            const signerUuid = generateUniqueId('s_');

            if (i === 0) {
                getActiveSigner(signerUuid);
            }

            const signerLi = $(`#signerUl li.signerLi:eq(${i})`);
            signerLi.attr('data-signer-uuid', signerUuid);

            if (!blank(item.label)) {
                if (i === 0) {
                    getActiveSigner(null, item.label);
                }

                signerLi.find('.signerLabel').html(item.label);
            }

            return {
                ...item,
                uuid: signerUuid,
                elements: item.elements.map((element) => ({
                    ...element,
                    uuid: generateUniqueId('e_'),
                    signer_label: item.label,
                    signer_uuid: signerUuid,
                })),
            };
        })
        .all();
</script>
