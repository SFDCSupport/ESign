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
        let currentScale = null;
        let selectedObject = null;
        let resizeTimeout = null;
        let isUpdatingSelection = false;
        let windowWidth = window.innerWidth;
        const pdfPreviewer = document.getElementById('previewViewer');
        const pdfViewer = $('#pdfViewer');
        const url = pdfViewer.attr('data-url');

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
                                `<div class="position-relative mt-1 ms-1 me-1 d-inline-block canvasContainer ${
                                    pageIndex === 0 ? 'active' : ''
                                }" data-canvas-index="${pageIndex}" id="canvas-container-${pageIndex}">
                                    <span class="pdfviewer-numbers canvasPdfNumber">${
                                        pageIndex + 1
                                    }</span>
                                    <canvas id="canvas-pdf-${pageIndex}" class="shadow-sm canvas-pdf"></canvas>
                                    <div class="position-absolute top-0 start-0">
                                        <canvas id="canvas-edition-${pageIndex}"></canvas>
                                    </div>
                                </div>`,
                            );

                            const canvasPdf = document.getElementById(
                                `canvas-pdf-${pageIndex}`,
                            );
                            const canvasEditionHTML = document.getElementById(
                                `canvas-edition-${pageIndex}`,
                            );
                            const context = canvasPdf.getContext('2d');

                            canvasPdf.page_index = pageIndex;
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

                                    const left =
                                        e.layerX ||
                                        e.originalEvent.layerX ||
                                        e.originalEvent.offsetX;
                                    const top =
                                        e.layerY ||
                                        e.originalEvent.layerY ||
                                        e.originalEvent.offsetY;

                                    const draggedData = JSON.parse(
                                        e.originalEvent.dataTransfer.getData(
                                            'text/plain',
                                        ),
                                    );
                                    const eleType = draggedData.eleType;
                                    const text = draggedData.text;
                                    const height = draggedData.height || 20;
                                    const width = draggedData.width || 60;
                                    const fontSize = 20;

                                    const obj = createFabricObject({
                                        eleType,
                                        text,
                                        height,
                                        width,
                                        left,
                                        top,
                                        fontSize,
                                    });

                                    canvasEdition.add(obj);

                                    $(document).trigger(
                                        'signer:element:added',
                                        {
                                            ...obj,
                                            on_page: pageIndex + 1,
                                            from: 'canvas',
                                        },
                                    );
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
                                    const target = e.target;

                                    if (isSigning && target) {
                                        const eleType = target.eleType;
                                        const on_page = target.on_page;
                                        const id = target.id;
                                        const obj = target;

                                        switch (eleType) {
                                            case 'signature_pad':
                                                const isSignatureObj = !blank(
                                                    obj.signature,
                                                );

                                                let data = {
                                                    eleType,
                                                    on_page,
                                                    obj,
                                                    id,
                                                };

                                                if (isSignatureObj) {
                                                    data = {
                                                        ...data,
                                                        signature:
                                                            obj.signature,
                                                    };
                                                }
                                                console.log(target);
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

                                        const width = obj.width * obj.scaleX;
                                        const height = obj.height * obj.scaleY;

                                        const left = obj.left;
                                        const top = obj.top;

                                        $(document).trigger(
                                            'signer:element:updated',
                                            {
                                                ...obj,
                                                width,
                                                height,
                                                left,
                                                top,
                                                from: 'canvas',
                                            },
                                        );
                                    }
                                })
                                .on('object:scaled', function (e) {
                                    console.log('scaled');
                                })
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

                            canvasEdition.page_index = pageIndex;
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
                `<div class="position-relative previewerContainer ${
                    index === 0 ? 'active' : ''
                }" data-canvas-index="${index}">
                  <canvas id="canvas-previewer-${index}"></canvas>
                  <span class="pdfviewer-numbers">${index + 1}</span>
                </div>`,
            );

            const canvas = document.getElementById(`canvas-previewer-${index}`);
            const context = canvas.getContext('2d');

            canvas.page_index = index;
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            canvas.addEventListener('click', function () {
                if (!blank(this.page_index)) {
                    $(document).trigger('move-to-canvas', this.page_index);
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
                $(document).trigger('signer:element:set-active', {
                    ...selectedObject,
                    from: 'canvas',
                });
            }

            if (isCleared) {
                selectedObject = null;
            } else {
                updateSelection.call(canvas);
            }
        };

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
            $(document).trigger('signer:element:removed', {
                ...target,
                from: 'canvas',
            });
        }

        function cloneObject(eventData, transform) {
            const target = transform.target;
            const canvas = target.canvas;
            const _uuid = generateUniqueId('e_');
            let obj;

            target.clone(function (cloned) {
                obj = cloned;
                cloned.left += 10;
                cloned.top += 10;
                cloned.uuid = _uuid;
                canvas.add(setFabricControl(cloned));
                canvas.setActiveObject(cloned);
            });

            canvas.requestRenderAll();

            $(document).trigger('signer:element:added', {
                ...obj,
                from: 'canvas',
            });
        }

        const setFabricControl = (fabricObject) => {
            fabricObject
                .setControlsVisibility({
                    mt: false,
                    mb: false,
                    ml: false,
                    mr: fabricObject.eleType !== 'signature_pad',
                    bl: false,
                    br: fabricObject.eleType === 'signature_pad',
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
            const _uuid = data.uuid || generateUniqueId('e_');
            let fabricObject;

            const commonStyles = {
                left: data.left,
                top: data.top,
                width: data.width,
                height: data.height,
                fontSize: data.fontSize || data.height,
                scaleX: currentScale ?? data.scale_x ?? 1,
                scaleY: currentScale ?? data.scale_y ?? 1,
                padding: 5,
                fill: '#333333',
                color: '#333333',
            };

            const text = $.trim(data.label ?? data.text ?? data.eleType);

            switch (data.eleType) {
                case 'text':
                case 'signature_pad':
                    fabricObject = new fabric.IText(text, {
                        ...commonStyles,
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

            fabricObject.id = data.id ?? undefined;
            fabricObject.eleType = data.eleType;
            fabricObject.on_page = data.on_page ?? undefined;
            fabricObject.label = data.label ?? undefined;
            fabricObject.uuid = _uuid;
            fabricObject.signer_uuid =
                data.signer_uuid || getActiveSigner() || null;
            fabricObject.is_required = data.is_required ?? true;
            fabricObject = setFabricControl(fabricObject);

            return fabricObject;
        };

        $(() => {
            try {
                delete fabric.Object.prototype.controls.mtr;
            } catch (e) {}

            $(document)
                .on('signer:reordered', (e, obj) => {
                    canvasEditions.forEach((canvasEdition) => {
                        const fromObj = canvasEdition
                            .getObjects()
                            .find((_obj) => _obj.signer_uuid === obj.uuid);
                        const toObj = canvasEdition
                            .getObjects()
                            .find((_obj) => _obj.signer_uuid === obj.withUuid);

                        if (!blank(fromObj)) {
                            fromObj.position = obj.withIndex;
                        }

                        if (!blank(toObj)) {
                            toObj.position = obj.index;
                        }
                    });
                })
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
                .on('signer:element:removed', function (e, obj) {
                    if (obj.from === 'canvas') {
                        return;
                    }

                    canvasEditions.forEach((canvasEdition) => {
                        const __obj = canvasEdition
                            .getObjects()
                            .find((_obj) => _obj.uuid === obj.uuid);

                        if (!blank(__obj)) {
                            canvasEdition.remove(__obj);
                        }
                    });
                })
                .on('load-pdf', function (e, obj) {
                    if (blank(obj.url) || !obj.container) {
                        return;
                    }
                    loadPDF(obj.url, obj.container);
                })
                .on('pad-to-fabric', function (e, obj) {
                    const oldObj = obj.obj;
                    const canvas = oldObj.canvas;

                    if (obj.eleType === 'signature_pad' && obj.signature) {
                        canvas.remove(oldObj);

                        fabric.Image.fromURL(obj.signature, (newImg) => {
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

                            newImg.id = obj.id;
                            newImg.on_page = obj.on_page;
                            newImg.eleType = obj.eleType;
                            newImg.signature = obj.signature;

                            canvas.add(newImg);
                        });
                    }
                });

            if (!blank(url)) {
                $(document).trigger('load-pdf', {
                    url: url,
                    container: pdfViewer[0],
                });

                loadedData = collect(@json(count($formattedData ?? 0) > 0 ? $formattedData : [['label' => '1st Signer', 'position' => 1, 'elements' => []]]))
                    .map((item, i) => {
                        const signerUuid = generateUniqueId('s_');

                        if (!isSigning) {
                            signerAdd({
                                from: 'loadedData',
                                signer_index: i + 1,
                                signer_uuid: signerUuid,
                                signer_label: item.label,
                            });
                        }

                        if (i === 0) {
                            getActiveSigner(signerUuid, item.label);
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

                $(document).on('canvas:ready', () => {
                    if (collect(loadedData).isNotEmpty()) {
                        canvasEditions.forEach((canvasEdition, i) => {
                            canvasEdition.clear();
                            if (i === 0) console.log(canvasEdition);
                            collect(loadedData)
                                .pluck('elements')
                                .flatten(1)
                                .each((objInfo, i) => {
                                    const objPage = objInfo?.on_page;
                                    const totalPages = canvasEditions.length;

                                    if (
                                        blank(objPage) ||
                                        objPage > totalPages
                                    ) {
                                        toast(
                                            'error',
                                            `Invalid element ${
                                                i + 1 + ' ' + objInfo?.eleType
                                            } position on page ${objPage} while total pages are ${totalPages}!`,
                                        );
                                        return;
                                    }

                                    if (
                                        objPage ===
                                        canvasEdition.page_index + 1
                                    ) {
                                        const obj = createFabricObject(objInfo);
                                        const cFor = isSigning
                                            ? {
                                                  for: 'signer',
                                              }
                                            : {};

                                        canvasEdition.add(obj);

                                        $(document).trigger(
                                            'signer:element:added',
                                            {
                                                ...cFor,
                                                ...objInfo,
                                                ...obj,
                                                from: 'loadedData',
                                            },
                                        );
                                    }
                                });

                            return true;
                        });

                        $(document).trigger('elements-added-to-canvas');
                    }
                });
            }

            $('.draggable').on('dragstart', function (e) {
                const _t = $(this);
                const eleType = _t.attr('data-type');
                const text = _t.find('span').text();
                const height = _t.attr('data-height') || 50;
                const width = _t.attr('data-width') || 100;

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
