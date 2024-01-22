<script>
    const getActiveSigner = () =>
        $('.selectedSigner[data-active-signer]').attr('data-active-signer');
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

            loadedData = collect(loadedData)
                .reject((i) => i.uuid === obj.uuid)
                .all();

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
                    on_page: obj.on_page,
                    signer_index: obj.signer_index,
                    type: obj.eleType,
                    offset_x: obj.offset_x,
                    offset_y: obj.offset_y,
                    width: obj.width,
                    height: obj.height,
                    is_required: obj.is_required,
                    signer_uuid: obj.signer_uuid,
                });
            }

            console.log('signer:element:added', loadedData);
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
                        .merge({
                            offset_x: obj.offset_x,
                            offset_y: obj.offset_y,
                            width: obj.width,
                            height: obj.height,
                            is_required: obj.is_required,
                        })
                        .all();
                }
            }

            console.log('signer:element:updated', loadedData);
        })
        .on('signer:element:removed', function (e, obj) {
            if (obj.from === 'loadedObject') {
                return;
            }

            const signerIndex = loadedData.search(
                (i) => i.uuid === obj.signer_uuid,
            );

            if (signerIndex !== false) {
                const elementIndex = collect(
                    loadedData[signerIndex].elements,
                ).search((e) => e.uuid === obj.uuid);

                if (elementIndex !== false) {
                    loadedData[signerIndex].elements = collect(
                        loadedData[signerIndex].elements,
                    )
                        .reject((e) => e.uuid === obj.uuid)
                        .all();
                }
            }

            console.log('signer:element:removed', loadedData);
        });

    let loadedData = collect([
        {
            id: 1,
            label: 'Signer 1',
            position: 1,
            elements: [
                {
                    on_page: 1,
                    type: 'signature_pad',
                    offset_x: 238.34674585238713,
                    offset_y: 112.34266801044906,
                    width: 184.46467700999992,
                    height: 37.25560053999998,
                    is_required: true,
                },
                {
                    on_page: 1,
                    type: 'signature_pad',
                    offset_x: 253.15437142614985,
                    offset_y: 218.27301736782994,
                    width: 126.44699999999999,
                    height: 25.537999999999993,
                    is_required: false,
                },
                {
                    on_page: 2,
                    type: 'text',
                    offset_x: 185.7008572647063,
                    offset_y: 50.38610868284016,
                    width: 33.95649999999999,
                    height: 25.537999999999993,
                    is_required: true,
                },
            ],
        },
        {
            id: 2,
            label: 'Signer 2',
            position: 2,
            elements: [
                {
                    on_page: 1,
                    type: 'email',
                    offset_x: 72,
                    offset_y: 28,
                    width: 47.2,
                    height: 22.599999999999998,
                    is_required: false,
                },
                {
                    on_page: 1,
                    type: 'textarea',
                    offset_x: 375,
                    offset_y: 20,
                    width: 68.3,
                    height: 22.599999999999998,
                    text: 'hello anand',
                    is_required: true,
                },
            ],
        },
    ])
        .map((item) => ({
            ...item,
            uuid: (signerUuid = generateUniqueId('s_')),
            elements: item.elements.map((element) => ({
                ...element,
                uuid: generateUniqueId('e_'),
                signer_label: item.label,
                signer_uuid: signerUuid,
            })),
        }))
        .all();
</script>
