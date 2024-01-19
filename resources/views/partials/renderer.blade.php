@pushonce('js')
    <script src="{{ url('vendor/esign/js/pdf.js') }}?legacy"></script>
    <script src="{{ url('/vendor/esign/js/fabric.min.js') }}?5.3.0"></script>
    <script>
        const isSigning = getSignerId();
        const rendered = {};
        const pdfRenderTasks = [];
        const pdfPages = [];
        const canvasEditions = [];
        const currentTextScale = 1;
        let selectedObject = null;
        let resizeTimeout = null;
        let isUpdatingSelection = false;
        let windowWidth = window.innerWidth;
        const pdfPreviewer = document.getElementById('previewViewer');
        const pdfViewer = $('#pdfViewer');
        const url = pdfViewer.data('url');

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
                            const viewerWidth = viewer.clientWidth - 40;

                            if (viewport.width > viewerWidth) {
                                viewport = page.getViewport({ scale: 1 });
                                scale = viewerWidth / viewport.width;
                                viewport = page.getViewport({ scale: scale });
                            }

                            currentScale = scale;

                            let pageIndex = page.pageNumber - 1;

                            renderThumbnailPreview(pageIndex, page);

                            viewer.insertAdjacentHTML(
                                'beforeend',
                                `
                            <div class="position-relative mt-1 ms-1 me-1 d-inline-block canvasContainer ${
                                pageIndex === 0 ? 'active' : ''
                            }" data-canvas-index="${pageIndex}" id="canvas-container-${pageIndex}">
                                <canvas id="canvas-pdf-${pageIndex}" class="shadow-sm canvas-pdf"></canvas>
                                <div class="position-absolute top-0 start-0">
                                    <canvas id="canvas-edition-${pageIndex}"></canvas>
                                </div>
                            </div>
                        `,
                            );

                            const canvasPdf = document.getElementById(
                                `canvas-pdf-${pageIndex}`,
                            );
                            const canvasEditionHTML = document.getElementById(
                                `canvas-edition-${pageIndex}`,
                            );
                            const context = canvasPdf.getContext('2d');

                            canvasPdf.pageId = pageIndex;
                            canvasPdf.height = viewport.height;
                            canvasPdf.width = viewport.width;
                            canvasEditionHTML.height = canvasPdf.height;
                            canvasEditionHTML.width = canvasPdf.width;

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

                                    const offsetX =
                                        e.layerX ||
                                        e.originalEvent.layerX ||
                                        e.originalEvent.offsetX;
                                    const offsetY =
                                        e.layerY ||
                                        e.originalEvent.layerY ||
                                        e.originalEvent.offsetY;

                                    const draggedData = JSON.parse(
                                        e.originalEvent.dataTransfer.getData(
                                            'text/plain',
                                        ),
                                    );
                                    const type = draggedData.type;
                                    const text = draggedData.text;
                                    const height = draggedData.height || 20;
                                    const width = draggedData.width || 60;
                                    const fontSize = 20;

                                    const fabricObject = createFabricObject({
                                        type,
                                        text,
                                        height,
                                        width,
                                        offsetX,
                                        offsetY,
                                        fontSize,
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
                                        const type = e.target.eleType;
                                        const obj = e.target;

                                        switch (type) {
                                            case 'signature_pad':
                                                const isSignatureObj = !blank(
                                                    obj.signature,
                                                );
                                                let data = {
                                                    type,
                                                    obj,
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
                                                    'Unknown type:',
                                                    type,
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
                                .on('text:changed', function (e) {})
                                .on('selection:created', function (e) {
                                    selectionChanged(this);
                                })
                                .on('selection:updated', function (e) {
                                    selectionChanged(this);
                                })
                                .on('selection:cleared', function () {
                                    if (!isUpdatingSelection) {
                                        selectionChanged(this, true);
                                    }
                                });

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

        const autoZoom = () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(resizePDF, 100);
        };

        const zoomChange = (inOrOut) => {
            if (resizeTimeout) {
                return;
            }

            let deltaScale = 0.2 * inOrOut;

            if (currentScale + deltaScale < 0) {
                return;
            }
            if (currentScale + deltaScale > 3) {
                return;
            }

            clearTimeout(resizeTimeout);
            currentScale += deltaScale;

            resizeTimeout = setTimeout(resizePDF(currentScale), 50);
        };

        const resizePDF = (scale = 'auto') => {
            let renderComplete = true;

            pdfRenderTasks.forEach(function (renderTask) {
                if (!renderTask) {
                    renderComplete = false;
                }
            });

            if (!renderComplete) {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function () {
                    resizePDF(scale);
                }, 50);
                return;
            }

            const _resize = (
                page,
                index,
                task,
                scale,
                width,
                selector,
                minus = 40,
                isPreviewer = false,
            ) => {
                if (
                    scale === 'auto' &&
                    page.getViewport({ scale: 1.5 }).width > width - minus
                ) {
                    scale =
                        (width - minus) / page.getViewport({ scale: 1 }).width;
                }

                if (scale === 'auto') {
                    scale = 1.5;
                }

                let viewport = page.getViewport({ scale: scale });

                if (!isPreviewer) {
                    currentScale = scale;
                }

                let canvasPDF = document.getElementById(selector);
                let context = canvasPDF.getContext('2d');
                canvasPDF.height = viewport.height;
                canvasPDF.width = viewport.width;

                if (!isPreviewer) {
                    canvasEdition = canvasEditions[index];

                    let scaleMultiplier = canvasPDF.width / canvasEdition.width;
                    let objects = canvasEdition.getObjects();
                    for (let i in objects) {
                        objects[i].scaleX = objects[i].scaleX * scaleMultiplier;
                        objects[i].scaleY = objects[i].scaleY * scaleMultiplier;
                        objects[i].left = objects[i].left * scaleMultiplier;
                        objects[i].top = objects[i].top * scaleMultiplier;
                        objects[i].setCoords();
                    }

                    canvasEdition.setWidth(
                        canvasEdition.getWidth() * scaleMultiplier,
                    );
                    canvasEdition.setHeight(
                        canvasEdition.getHeight() * scaleMultiplier,
                    );
                    canvasEdition.renderAll();
                    canvasEdition.calcOffset();
                }

                let renderContext = {
                    canvasContext: context,
                    viewport: viewport,
                    enhanceTextSelection: !isPreviewer,
                };

                if (isPreviewer) {
                    task = page.render(renderContext);
                    task.promise.then(function () {
                        clearTimeout(resizeTimeout);
                        resizeTimeout = null;
                    });
                } else {
                    task.cancel();
                    pdfRenderTasks[index] = null;
                    task = page.render(renderContext);
                    task.promise.then(function () {
                        pdfRenderTasks[index] = task;
                        clearTimeout(resizeTimeout);
                        resizeTimeout = null;
                    });
                }
            };

            pdfPages.forEach(function (page, i) {
                let renderTask = pdfRenderTasks[i];
                const viewerWidth = pdfViewer[0].clientWidth;
                const previewerWidth = pdfPreviewer.clientWidth;

                _resize(
                    page,
                    i,
                    renderTask,
                    scale,
                    viewerWidth,
                    'canvas-pdf-' + i,
                );
                _resize(
                    page,
                    i,
                    renderTask,
                    scale,
                    previewerWidth,
                    'canvas-previewer-' + i,
                    10,
                    true,
                );
            });
        };

        const renderThumbnailPreview = (index, page) => {
            let scale = 1.5;
            let viewport = page.getViewport({
                scale: scale,
            });
            let viewerWidth = pdfPreviewer.clientWidth - 10;

            if (viewport.width > viewerWidth) {
                viewport = page.getViewport({ scale: 1 });
                scale = viewerWidth / viewport.width;
                viewport = page.getViewport({ scale: scale });
            }

            pdfPreviewer.insertAdjacentHTML(
                'beforeend',
                `
                <div class="position-relative previewerContainer ${
                    index === 0 ? 'active' : ''
                }" data-canvas-index="${index}">
                  <canvas id="canvas-previewer-${index}"></canvas>
                  <span class="pdfviewer-numbers">1</span>
                </div>`,
            );

            const canvas = document.getElementById(`canvas-previewer-${index}`);
            const context = canvas.getContext('2d');

            canvas.pageId = index;
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            canvas.addEventListener('click', function () {
                if (!blank(this.pageId)) {
                    $(document).trigger('move-to-canvas', this.pageId);
                }
            });

            const pdfRenderTask = page.render({
                canvasContext: context,
                viewport: viewport,
            });

            pdfRenderTask.promise.then(() => {
                console.log(`Preview page rendered`);
            });
        };

        function updateSelection() {
            isUpdatingSelection = true;

            for (let i = 0; i < canvasEditions.length; i++) {
                const canvas = canvasEditions[i];

                if (canvas !== this) {
                    canvas.discardActiveObject().renderAll();
                }
            }

            isUpdatingSelection = false;
        }

        const selectionChanged = (canvas, isCleared = false) => {
            selectedObject = isCleared
                ? selectedObject
                : canvas.getActiveObject();

            if (!blank((uuid = selectedObject?.uuid || null))) {
                $(document).trigger('signer-element:active', uuid);
            }

            if (isCleared) {
                selectedObject = null;
            } else {
                updateSelection.call(canvas);
            }
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
                        on_page: canvasEdition.pageIndex + 1,
                        type: obj.eleType,
                        offsetX: obj.left,
                        offsetY: obj.top,
                        width: obj.width,
                        height: obj.height,
                        signerIndex: obj.signerIndex,
                    });
                });
            });

            $(document).trigger('signers-save');
        };

        const triggerSignerElementAdd = (uuid, type, index, text = null) =>
            $(document).trigger('signer-element:add', {
                uuid: uuid,
                type: type,
                signerIndex: index,
                text: text,
            });

        fabric.Canvas.prototype.getAbsoluteCoords = function (object) {
            return {
                left: object.left + this._offset.left,
                top: object.top + this._offset.top,
            };
        };

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
                ctx.rotate(fabric.util.degreesToRadians(fabricObject.angle));
                ctx.drawImage(icon, -size / 2, -size / 2, size, size);
                ctx.restore();
            };
        }

        function deleteObject(eventData, transform) {
            const target = transform.target;
            const canvas = target.canvas;

            canvas.remove(target);
            canvas.requestRenderAll();
            $(document).trigger('signer-element:remove', target.uuid);
        }

        function cloneObject(eventData, transform) {
            const target = transform.target;
            const canvas = target.canvas;
            const _uuid = generateUniqueId();

            target.clone(function (cloned) {
                cloned.left += 10;
                cloned.top += 10;
                cloned.uuid = _uuid;
                canvas.add(setFabricControl(cloned));
                canvas.setActiveObject(cloned);
            });

            canvas.requestRenderAll();

            triggerSignerElementAdd(
                _uuid,
                target.eleType,
                target.signerIndex,
                target.text,
            );
        }

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
                              hoverCursor: 'pointer',
                          }
                        : {
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
            const _uuid = generateUniqueId();
            let fabricObject;

            const commonStyles = {
                left: data.offset_x,
                top: data.offset_y,
                width: data.width,
                height: data.height,
                fontSize: data.fontSize || data.height,
                padding: 5,
                fill: '#333333',
                color: '#333333',
            };

            const text = $.trim(data.text || data.type);

            switch (data.type) {
                case 'text':
                case 'signature_pad':
                    fabricObject = new fabric.IText(text, {
                        ...commonStyles,
                        scaleX: 1,
                        scaleY: 1,
                    });
                    break;
                default:
                    fabricObject = new fabric.Text(text, commonStyles);
                    break;
            }

            if (!isSigning) {
                const deleteIcon = svgToDataUrl(`@include('esign::partials.icons.x', ['stroke' => 'red'])`);
                const cloneIcon = svgToDataUrl(`@include('esign::partials.icons.copy', ['stroke' => 'blue'])`);

                const deleteImg = document.createElement('img');
                deleteImg.classList.add('icon-delete');
                deleteImg.src = deleteIcon;

                const cloneImg = document.createElement('img');
                deleteImg.classList.add('icon-clone');
                cloneImg.src = cloneIcon;

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

            fabricObject.eleType = data.type;
            fabricObject.uuid = _uuid;
            fabricObject.signerIndex =
                data.signerIndex || getActiveSignerIndex();

            triggerSignerElementAdd(
                _uuid,
                data.type,
                fabricObject.signerIndex,
                data.text || fabricObject.text || data.type,
            );

            return fabricObject;
        };

        $(() => {
            try {
                delete fabric.Object.prototype.controls.mtr;
            } catch (e) {}

            $(document)
                .on('move-to-canvas', (e, index) => {
                    const dataIndexSelector = $(
                        `div[data-canvas-index="${index}"]`,
                    );

                    $('div[data-canvas-index]').removeClass('active');
                    dataIndexSelector.addClass('active');
                    dataIndexSelector.each((i, ele) => {
                        $('html, body').animate(
                            {
                                scrollTop: $(ele).offset().top,
                            },
                            400,
                        );
                    });
                })
                .on('signer-element:remove', (e, uuid) => {
                    canvasEditions.forEach((canvasEdition) => {
                        const obj = canvasEdition
                            .getObjects()
                            .find((_obj) => _obj.uuid === uuid);

                        if (!blank(obj)) {
                            canvasEdition.remove(obj);
                        }
                    });
                })
                .on('load-pdf', (e, data) => {
                    if (blank(data.url) || !data.container) {
                        return;
                    }
                    loadPDF(data.url, data.container);
                })
                .on('pad-to-fabric', (e, data) => {
                    const oldObj = data.obj;
                    const canvas = oldObj.canvas;

                    if (data.type === 'signature_pad' && data.signature) {
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

                            newImg.eleType = data.type;
                            newImg.signature = data.signature;

                            canvas.add(newImg);
                        });
                    }
                });

            if (!blank(url)) {
                $(document).trigger('load-pdf', {
                    url: url,
                    container: pdfViewer[0],
                });

                $(document).on('canvas:ready', () => {
                    const loadedObjectData = [
                        {
                            on_page: 1,
                            signer_id: 1,
                            type: 'signature_pad',
                            offset_x: 238.34674585238713,
                            offset_y: 112.34266801044906,
                            width: 184.46467700999992,
                            height: 37.25560053999998,
                            signerIndex: '1',
                        },
                        {
                            on_page: 1,
                            signer_id: 2,
                            type: 'signature_pad',
                            offset_x: 253.15437142614985,
                            offset_y: 218.27301736782994,
                            width: 126.44699999999999,
                            height: 25.537999999999993,
                            signerIndex: '1',
                        },
                        {
                            on_page: 1,
                            signer_id: 3,
                            type: 'email',
                            offset_x: 72,
                            offset_y: 28,
                            width: 47.2,
                            height: 22.599999999999998,
                            signerIndex: '3',
                        },
                        {
                            on_page: 1,
                            signer_id: 4,
                            type: 'textarea',
                            offset_x: 375,
                            offset_y: 20,
                            width: 68.3,
                            height: 22.599999999999998,
                            signerIndex: '3',
                            text: 'hello anand',
                        },
                        {
                            on_page: 2,
                            signer_id: 5,
                            type: 'text',
                            offset_x: 185.7008572647063,
                            offset_y: 50.38610868284016,
                            width: 33.95649999999999,
                            height: 25.537999999999993,
                            position: '1',
                        },
                    ];

                    if (!blank(loadedObjectData)) {
                        $.when(
                            canvasEditions.forEach((canvasEdition) => {
                                canvasEdition.clear();

                                loadedObjectData.forEach((objInfo, i) => {
                                    const objPage = objInfo.on_page;
                                    const totalPages = canvasEditions.length;

                                    if (
                                        blank(objPage) ||
                                        objPage > totalPages
                                    ) {
                                        toast(
                                            'error',
                                            `Invalid element ${
                                                i + 1 + ' ' + objInfo.type
                                            } position on page ${objPage} while total pages are ${totalPages}!`,
                                        );
                                        return;
                                    }

                                    if (
                                        objInfo.on_page ===
                                        canvasEdition.pageIndex + 1
                                    ) {
                                        const newObj2 =
                                            createFabricObject(objInfo);

                                        canvasEdition.add(newObj2);
                                    }
                                });

                                return true;
                            }),
                        ).then(function () {
                            $(document).trigger('elements-added-to-canvas');
                        });
                    }
                });
            }

            $('.draggable').on('dragstart', function (e) {
                const type = $(this).data('type');
                const text = $(this).find('span').text();
                const height = $(this).data('height') || 50;
                const width = $(this).data('width') || 100;

                const data = {
                    type,
                    text,
                    height,
                    width,
                };

                e.originalEvent.dataTransfer.setData(
                    'text/plain',
                    JSON.stringify(data),
                );
            });

            $(window).on('resize', function (e) {
                e.preventDefault() && e.stopPropagation();

                if (windowWidth === window.innerWidth) {
                    return;
                }

                windowWidth = window.innerWidth;
                autoZoom();
            });
        });
    </script>
@endpushonce

@include('esign::documents.modals.signing-modal')
