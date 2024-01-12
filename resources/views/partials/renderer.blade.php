@pushonce('js')
    <script src="{{ url('vendor/esign/js/pdf.js') }}?legacy"></script>
    <script src="{{ url('/vendor/esign/js/fabric.min.js') }}?4.6.0"></script>
    <script src="{{ url('vendor/esign/js/signature_pad.umd.min.js') }}?3.0.0-beta.3"></script>
    <script>
        let addLock = true;
        let forceAddLock = true;
        let currentScale = 1.5;
        let currentCursor = null;
        let currentTextScale = 1;
        let activeCanvas = null;
        let activeCanvasPointer = null;
        let hasModifications = false;

        const pdfRenderTasks = [];
        const pdfPages = [];
        const canvasEditions = [];
        const svgCollections = [];

        const loadPDF = (url, viewer) => {
            const pdfjsLib = window['pdfjs-dist/build/pdf'];

            pdfjsLib.GlobalWorkerOptions.workerSrc =
                '/vendor/esign/js/pdf.worker.js?legacy';

            pdfjsLib.getDocument(url).promise.then((pdfDoc) => {
                const numPages = pdfDoc.numPages;

                for (let pageNum = 1; pageNum <= numPages; pageNum++) {
                    pdfDoc.getPage(pageNum).then((page) => {
                        let scale = 1.5;
                        let viewport = page.getViewport({ scale: scale });

                        if (viewport.width > viewer[0].clientWidth - 40) {
                            viewport = page.getViewport({ scale: 1 });
                            scale =
                                (viewer[0].clientWidth - 40) / viewport.width;
                            viewport = page.getViewport({ scale: scale });
                        }

                        currentScale = scale;
                        let pageIndex = page.pageNumber - 1;

                        viewer.append(`
                            <div class="position-relative mt-1 ms-1 me-1 d-inline-block" id="canvas-container-${pageIndex}">
                                <canvas id="canvas-pdf-${pageIndex}" class="shadow-sm canvas-pdf"></canvas>
                                <div class="position-absolute top-0 start-0">
                                    <canvas id="canvas-edition-${pageIndex}"></canvas>
                                </div>
                            </div>
                        `);

                        const canvasPdf = $(`#canvas-pdf-${pageIndex}`);
                        const canvasEditionHTML = $(
                            `#canvas-edition-${pageIndex}`,
                        );
                        const context = canvasPdf[0].getContext('2d');

                        canvasPdf[0].height = viewport.height;
                        canvasPdf[0].width = viewport.width;
                        canvasEditionHTML[0].height = canvasPdf[0].height;
                        canvasEditionHTML[0].width = canvasPdf[0].width;

                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport,
                            enhanceTextSelection: true,
                        };

                        const renderTask = page.render(renderContext);

                        pdfRenderTasks.push(renderTask);
                        pdfPages.push(page);

                        const canvasEdition = new fabric.Canvas(
                            'canvas-edition-' + pageIndex,
                            {
                                selection: false,
                                allowTouchScrolling: true,
                            },
                        );

                        $(`#canvas-container-${pageIndex}`).on('drop', (e) => {
                            console.log('e');
                        });

                        canvasEdition
                            .on('mouse:move', function (e) {
                                activeCanvas = this;
                                activeCanvasPointer = e.pointer;
                            })
                            .on('mouse:down:before', function (e) {
                                currentCursor = this.defaultCursor;
                            })
                            .on('mouse:down', function (e) {
                                if (e.target) {
                                    this.defaultCursor = 'default';

                                    return;
                                }

                                const selectedInput = $(
                                    'input[name="svg-to-add"]:checked',
                                );

                                if (
                                    currentCursor === 'default' &&
                                    selectedInput.length > 0
                                ) {
                                    this.defaultCursor = 'copy';
                                }

                                if (currentCursor !== 'copy') {
                                    return;
                                }

                                if (selectedInput.length <= 0) {
                                    return;
                                }

                                createAndAddSVGToCanvas(
                                    this,
                                    selectedInput.val(),
                                    e.pointer.x,
                                    e.pointer.y,
                                    selectedInput.data().length,
                                );

                                if (addLock) {
                                    return;
                                }

                                selectedInput
                                    .prop('checked', true)
                                    .trigger('change');
                            })
                            .on('object:scaling', (e) => {
                                if (e.transform.action === 'scaleX') {
                                    e.target.scaleY = e.target.scaleX;
                                }
                                if (e.transform.action === 'scaleY') {
                                    e.target.scaleX = e.target.scaleY;
                                }
                            })
                            .on('object:scaled', function (e) {
                                if (e.target instanceof fabric.IText) {
                                    currentTextScale = e.target.scaleX;
                                    return;
                                }

                                const item = getSvgItem(e.target.svgOrigin);

                                if (item) {
                                    item.scale =
                                        (e.target.width * e.target.scaleX) /
                                        e.target.canvas.width;
                                }
                            })
                            .on('text:changed', function (e) {
                                if ((!e.target) instanceof fabric.IText) {
                                    return;
                                }

                                const textLinesMaxWidth =
                                    e.target.textLines.reduce(
                                        (max, _, i) =>
                                            Math.max(
                                                max,
                                                e.target.getLineWidth(i),
                                            ),
                                        0,
                                    );
                                e.target.set({ width: textLinesMaxWidth });
                            });

                        canvasEditions.push(canvasEdition);
                    });
                }

                $(document).trigger('canvas:ready');
            });
        };

        const getSvgItem = (svg) => {
            for (let i in svgCollections) {
                const svgItem = svgCollections[i];

                if (svgItem.svg === svg) {
                    return svgItem;
                }
            }

            return null;
        };

        const addObjectInCanvas = (canvas, item) => {
            item.on('selected', function (event) {
                $(
                    '#svg_object_actions,#svg_selected_container, #btn_svn_select',
                ).removeClass('d-none');
            });

            item.on('deselected', function (event) {
                if ($('input[name="svg_to_add"]:checked').length > 0) {
                    $('#svg_selected_container').removeClass('d-none');
                } else {
                    $('btn_svn_select').removeClass('d-none');
                }
                $('svg_object_actions').addClass('d-none');
            });

            return canvas.add(item);
        };

        const createAndAddSVGToCanvas = (canvas, item, x, y, height = null) => {
            $('#saveBtn').removeAttr('disabled').prop('disabled', false);

            hasModifications = true;

            height = height || 100;

            if (item === 'text') {
                let textbox = new fabric.Textbox('Text to modify', {
                    left: x,
                    top: y - 20,
                    fontSize: 20,
                    direction: direction,
                    fontFamily: 'Monospace',
                });

                addObjectInCanvas(canvas, textbox).setActiveObject(textbox);

                textbox.keysMap[13] = 'exitEditing';
                textbox.lockScalingFlip = true;
                textbox.scaleX = currentTextScale;
                textbox.scaleY = currentTextScale;
                textbox.enterEditing();
                textbox.selectAll();

                return;
            }

            fabric.loadSVGFromURL(item, function (objects, options) {
                let svg = fabric.util.groupSVGElements(objects, options);

                svg.svgOrigin = item;
                svg.lockScalingFlip = true;
                svg.scaleToHeight(height);

                if (svg.getScaledWidth() > 200) {
                    svg.scaleToWidth(200);
                }

                let svgItem = getSvgItem(item);

                if (svgItem && svgItem.scale) {
                    svg.scaleToWidth(canvas.width * svgItem.scale);
                }

                svg.top = y - svg.getScaledHeight() / 2;
                svg.left = x - svg.getScaledWidth() / 2;

                addObjectInCanvas(canvas, svg);
            });
        };

        const renderElements = (elements) => {};

        $(() => {
            const pdfViewer = $('#pdfViewer');
            const url = pdfViewer.data('url');

            if (url) {
                loadPDF(url, pdfViewer);
            }
        });
    </script>
@endpushonce
