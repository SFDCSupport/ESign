@pushonce('js')
    <script src="{{ url('vendor/esign/js/pdf.js') }}?legacy"></script>
    <script src="{{ url('/vendor/esign/js/fabric.min.js') }}?5.3.0"></script>
    <script>
        const isSigning = getSignerId();
        const rendered = {};
        const pdfRenderTasks = [];
        const pdfPages = [];
        const canvasEditions = [];

        const loadPDF = (url, viewer) => {
            const pdfjsLib = window['pdfjs-dist/build/pdf'];

            pdfjsLib.GlobalWorkerOptions.workerSrc =
                '/vendor/esign/js/pdf.worker.js?legacy';

            $(document).trigger('loader:show');

            pdfjsLib
                .getDocument(url)
                .promise.then((pdfDoc) => {
                    const numPages = pdfDoc.numPages;

                    toast('info', `Loading page: ${numPages}`);

                    const promises = [];

                    for (let pageNum = 1; pageNum <= numPages; pageNum++) {
                        const promise = pdfDoc.getPage(pageNum).then((page) => {
                            let scale = 1.5;
                            let viewport = page.getViewport({ scale: scale });

                            if (viewport.width > viewer.clientWidth - 40) {
                                viewport = page.getViewport({ scale: 1 });
                                scale =
                                    (viewer.clientWidth - 40) / viewport.width;
                                viewport = page.getViewport({ scale: scale });
                            }

                            currentScale = scale;

                            let pageIndex = page.pageNumber - 1;

                            if (pageNum === 1) {
                                const pdfPreviewer =
                                    document.getElementById('previewViewer');
                                const pdfPreviewerCanvas =
                                    document.createElement('canvas');
                                pdfPreviewer.appendChild(pdfPreviewerCanvas);
                                const pdfPreviewerContext =
                                    pdfPreviewerCanvas.getContext('2d');
                                const pdfPreviewerViewport = page.getViewport({
                                    scale: 1,
                                });

                                pdfPreviewerCanvas.height =
                                    pdfPreviewer.clientHeight;
                                pdfPreviewerCanvas.width =
                                    pdfPreviewer.clientWidth;

                                const pdfPreviewerScale = Math.min(
                                    pdfPreviewerCanvas.width /
                                        pdfPreviewerViewport.width,
                                    pdfPreviewerCanvas.height /
                                        pdfPreviewerViewport.height,
                                );
                                const pdfPreviewerScaledViewport =
                                    page.getViewport({ pdfPreviewerScale });

                                const pdfPreviewerRenderTask = page.render({
                                    canvasContext: pdfPreviewerContext,
                                    viewport: pdfPreviewerScaledViewport,
                                    transform: [
                                        1,
                                        0,
                                        0,
                                        -1,
                                        0,
                                        pdfPreviewerCanvas.height,
                                    ],
                                });

                                pdfPreviewerRenderTask.promise.then(() => {
                                    console.log(`Preview page rendered`);
                                });
                            }

                            viewer.insertAdjacentHTML(
                                'beforeend',
                                `
                            <div class="position-relative mt-1 ms-1 me-1 d-inline-block" id="canvas-container-${pageIndex}">
                                <canvas id="canvas-pdf-${pageIndex}" class="shadow-sm canvas-pdf"></canvas>
                                <div class="position-absolute top-0 start-0">
                                    <canvas id="canvas-edition-${pageIndex}"></canvas>
                                </div>
                            </div>
                        `,
                            );

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

                            renderTask.promise.then(
                                () => {
                                    console.log(
                                        `Page ${pageNum} successfully rendered.`,
                                    );
                                },
                                (error) => {
                                    toast(
                                        'error',
                                        `Error rendering page ${pageNum}: ${error}`,
                                    );
                                    console.error(
                                        `Error rendering page ${pageNum}:`,
                                        error,
                                    );
                                },
                            );

                            pdfRenderTasks.push(renderTask);
                            pdfPages.push(page);

                            const canvasEdition = new fabric.Canvas(
                                'canvas-edition-' + pageIndex,
                                {
                                    selection: false,
                                    allowTouchScrolling: true,
                                },
                            );

                            $(`#canvas-container-${pageIndex}`).on(
                                'drop',
                                function (e) {
                                    e.preventDefault();

                                    if (isSigning) {
                                        toast(
                                            'warning',
                                            "Can't add elements in signing mode!",
                                        );
                                        return;
                                    }

                                    const offsetX =
                                        e.originalEvent.clientX -
                                        e.currentTarget.offsetLeft;
                                    const offsetY =
                                        e.originalEvent.clientY -
                                        e.currentTarget.offsetTop;

                                    const draggedData = JSON.parse(
                                        e.originalEvent.dataTransfer.getData(
                                            'text/plain',
                                        ),
                                    );
                                    const eleType = draggedData.eleType;
                                    const text = draggedData.text;
                                    const height = draggedData.height || 20;
                                    const width = draggedData.width || 60;

                                    const fabricObject = createFabricObject({
                                        eleType,
                                        text,
                                        height,
                                        width,
                                        offsetX,
                                        offsetY,
                                    });

                                    canvasEdition.add(fabricObject);
                                },
                            );

                            canvasEdition
                                .on('keydown', function (e) {
                                    const activeObject =
                                        canvasEdition.getActiveObject();

                                    if (isSigning || blank(activeObject)) {
                                        return;
                                    }

                                    if (
                                        (e.ctrlKey || e.metaKey) &&
                                        (e.key === 'c' || e.key === 'x')
                                    ) {
                                    }

                                    if (
                                        (e.ctrlKey || e.metaKey) &&
                                        e.key === 'v' &&
                                        fabric.copiedObject
                                    ) {
                                    }

                                    if (
                                        e.key === 'Delete' ||
                                        e.key === 'Backspace' ||
                                        e.key === 'Del'
                                    ) {
                                    }
                                })
                                .on('mouse:move', function (e) {})
                                .on('mouse:down:before', function (e) {})
                                .on('mouse:down', function (e) {
                                    if (isSigning && e.target) {
                                        const eleType = e.target.eleType;
                                        const obj = e.target;

                                        switch (eleType) {
                                            case 'signature_pad':
                                                const isSignatureObj = !blank(
                                                    obj.signature,
                                                );
                                                let data = {
                                                    eleType: eleType,
                                                    obj: obj,
                                                };

                                                if (isSignatureObj) {
                                                    data = {
                                                        ...data,
                                                        signature:
                                                            obj.signature,
                                                    };
                                                }

                                                $(document).trigger(
                                                    isSignatureObj
                                                        ? 'fabric-to-pad'
                                                        : 'signing-modal:show',
                                                    data,
                                                );
                                                break;
                                            case 'text':
                                                obj.set({
                                                    selectable: true,
                                                });

                                                break;
                                            default:
                                                console.warn(
                                                    'Unknown eleType:',
                                                    eleType,
                                                );

                                                break;
                                        }
                                    }
                                })
                                .on('object:scaling', function (e) {})
                                .on('object:modified', function (e) {
                                    if (e.target) {
                                        const obj = e.target;
                                        const canvas = obj.canvas;

                                        if (obj.left < 0) {
                                            obj.set({ left: 0 });
                                        }

                                        if (obj.top < 0) {
                                            obj.set({ top: 0 });
                                        }

                                        if (
                                            obj.left + obj.width >
                                            canvas.width
                                        ) {
                                            obj.set({
                                                left: canvas.width - obj.width,
                                            });
                                        }

                                        if (
                                            obj.top + obj.height >
                                            canvas.height
                                        ) {
                                            obj.set({
                                                top: canvas.height - obj.height,
                                            });
                                        }

                                        canvas.renderAll();
                                    }
                                })
                                .on('object:scaled', function (e) {})
                                .on('text:changed', function (e) {});

                            canvasEdition.pageIndex = pageIndex;
                            canvasEditions.push(canvasEdition);
                        });

                        promises.push(promise);
                    }

                    Promise.all(promises).then(() => {
                        $(document).trigger('canvas:ready');
                    });
                })
                .catch((error) => {
                    toast('error', `Error loading PDF: ${error}`);
                    console.error('Error loading PDF:', error);
                })
                .finally(() => {
                    $(document).trigger('loader:hide');
                    toast('success', `PDF loading process completed.`);
                    console.log('PDF loading process completed.');
                });

            return true;
        };

        const saveBtnAction = () => {
            canvasEditions.forEach((canvasEdition, pageIndex) => {
                canvasEdition.forEachObject((obj) => {
                    let additionalInfo = {};

                    if (isSigning) {
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
                    }

                    console.log('Object Info:', {
                        ...additionalInfo,
                        page: canvasEdition.pageIndex + 1,
                        eleType: obj.eleType,
                        offsetX: obj.left,
                        offsetY: obj.top,
                        width: obj.width,
                        height: obj.height,
                    });
                });
            });
        };

        const setFabricControl = (fabricObject) => {
            fabricObject
                .setControlsVisibility({
                    mt: false,
                    mb: false,
                    ml: false,
                    mr: false,
                    bl: false,
                    br: true,
                    tl: false,
                    tr: false,
                })
                .set(
                    isSigning
                        ? {
                              selectable: false,
                              lockScaling: true,
                              lockMovement: true,
                              hoverCusror: 'pointer',
                          }
                        : {
                              fontSize: 14,
                              selectable: true,
                              hasControls: true,
                              hasBorders: true,
                              centerTransform: true,
                              lockUniScaling: true,
                          },
                )
                .set({
                    transparentCorners: false,
                    objectCaching: false,
                    hasRotatingPoint: false,
                    cornerColor: 'blue',
                    cornerStyle: 'circle',
                    originX: 'center',
                    originY: 'center',
                    textAlign: 'center',
                    backgroundColor: `rgb(255, 177, 171, 0.5)`,
                });

            return fabricObject;
        };

        const createFabricObject = (data) => {
            let fabricObject;

            fabric.Canvas.prototype.getAbsoluteCoords = function (object) {
                return {
                    left: object.left + this._offset.left,
                    top: object.top + this._offset.top,
                };
            };

            const commonStyles = {
                left: data.offsetX,
                top: data.offsetY,
                width: data.width,
                height: data.height,
                padding: 5,
                fill: '#333333',
                color: '#333333',
            };

            const text = $.trim(data.text || data.eleType);

            switch (data.eleType) {
                default:
                    fabricObject = new fabric.Text(text, commonStyles);
                    break;
            }

            if (!isSigning) {
                const deleteIcon = svgToDataUrl(`@include('esign::partials.icons.x')`);
                const cloneIcon = svgToDataUrl(`@include('esign::partials.icons.copy')`);

                const deleteImg = document.createElement('img');
                deleteImg.src = deleteIcon;

                const cloneImg = document.createElement('img');
                cloneImg.src = cloneIcon;

                function renderIcon(icon) {
                    return function renderIcon(
                        ctx,
                        left,
                        top,
                        styleOverride,
                        fabricObject,
                    ) {
                        const size = this.cornerSize;

                        ctx.save();
                        ctx.translate(left, top);
                        ctx.rotate(
                            fabric.util.degreesToRadians(fabricObject.angle),
                        );
                        ctx.drawImage(icon, -size / 2, -size / 2, size, size);
                        ctx.restore();
                    };
                }

                function deleteObject(eventData, transform) {
                    const target = transform.target;
                    const canvas = target.canvas;

                    canvas.remove(target);
                    canvas.requestRenderAll();
                }

                function cloneObject(eventData, transform) {
                    const target = transform.target;
                    const canvas = target.canvas;

                    target.clone(function (cloned) {
                        cloned.left += 10;
                        cloned.top += 10;
                        canvas.add(setFabricControl(cloned));
                        canvas.setActiveObject(cloned);
                    });

                    canvas.requestRenderAll();
                }

                fabric.Object.prototype.controls.deleteControl =
                    new fabric.Control({
                        x: 0.5,
                        y: -0.4,
                        offsetY: -16,
                        offsetX: 0,
                        cursorStyle: 'pointer',
                        mouseUpHandler: deleteObject,
                        render: renderIcon(deleteImg),
                        cornerSize: 18,
                    });

                fabric.Object.prototype.controls.cloneControl =
                    new fabric.Control({
                        x: 0.5,
                        y: -0.4,
                        offsetY: -16,
                        offsetX: -20,
                        cursorStyle: 'pointer',
                        mouseUpHandler: cloneObject,
                        render: renderIcon(cloneImg),
                        cornerSize: 18,
                    });
            }

            fabricObject = setFabricControl(fabricObject);

            fabricObject.eleType = data.eleType;

            return fabricObject;
        };

        $(() => {
            try {
                delete fabric.Object.prototype.controls.mtr;
            } catch (e) {}

            $(document)
                .on('load-pdf', (e, data) => {
                    if (blank(data.url) || !data.container) {
                        return;
                    }
                    loadPDF(data.url, data.container);
                })
                .on('pad-to-fabric', (e, data) => {
                    const oldObj = data.obj;
                    const canvas = oldObj.canvas;

                    if (data.eleType === 'signature_pad' && data.signature) {
                        canvas.remove(oldObj);

                        fabric.Image.fromURL(data.signature, (newImg) => {
                            newImg.set({
                                left: oldObj.left,
                                top: oldObj.top,
                            });

                            const scaleX = oldObj.width / newImg.width;
                            const scaleY = oldObj.height / newImg.height;
                            const minScale = Math.min(scaleX, scaleY);
                            const customScale =
                                oldObj instanceof fabric.Image ? 0.23 : null;

                            newImg.scaleToWidth(
                                newImg.width * (customScale ?? minScale),
                            );
                            newImg.scaleToHeight(
                                newImg.height * (customScale ?? minScale),
                            );

                            setFabricControl(newImg);

                            newImg.eleType = data.eleType;
                            newImg.signature = data.signature;

                            canvas.add(newImg);
                        });
                    }
                });

            const pdfViewer = $('#pdfViewer');
            const url = pdfViewer.data('url');

            if (!blank(url)) {
                $(document).trigger('load-pdf', {
                    url: url,
                    container: pdfViewer[0],
                });

                $(document).on('canvas:ready', () => {
                    const loadedObjectData = [
                        {
                            page: 1,
                            eleType: 'signature_pad',
                            offsetX: 202,
                            offsetY: 150,
                            width: 61.28,
                            height: 58.08,
                        },
                        {
                            page: 1,
                            eleType: 'text',
                            offsetX: 295,
                            offsetY: 320,
                            width: 204.4,
                            height: 60.02559999999999,
                        },
                        {
                            page: 1,
                            eleType: 'email',
                            offsetX: 332,
                            offsetY: 216,
                            width: 213.76000000000002,
                            height: 60.02559999999999,
                        },
                    ];

                    if (isSigning && !blank(loadedObjectData)) {
                        canvasEditions.forEach((canvasEdition) => {
                            canvasEdition.clear();

                            loadedObjectData.forEach((objInfo) => {
                                if (
                                    objInfo.page ===
                                    canvasEdition.pageIndex + 1
                                ) {
                                    const newObj2 = createFabricObject(objInfo);

                                    canvasEdition.add(newObj2);
                                }
                            });
                        });
                    }
                });
            }

            if (!isSigning) {
                $('.draggable').on('dragstart', function (e) {
                    const eleType = $(this).data('type');
                    const text = $(this).find('span').text();
                    const height = $(this).data('height') || 50;
                    const width = $(this).data('width') || 100;

                    const data = {
                        eleType,
                        text,
                        height,
                        width,
                    };

                    e.originalEvent.dataTransfer.setData(
                        'text/plain',
                        JSON.stringify(data),
                    );
                });
            }
        });
    </script>
@endpushonce

@include('esign::documents.modals.signing-modal')
