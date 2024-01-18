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
        let isUpdatingSelection = false;

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
                                    const eleType = draggedData.eleType;
                                    const text = draggedData.text;
                                    const height = draggedData.height || 20;
                                    const width = draggedData.width || 60;
                                    const fontSize = 20;

                                    const fabricObject = createFabricObject({
                                        eleType,
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

        const pdfPreviewer = document.getElementById('previewViewer');
        const renderThumbnailPreview = (index, page) => {
            let scale = 1.5;
            let viewport = page.getViewport({
                scale: scale,
            });
            let viewerWidth = pdfPreviewer.clientWidth - 20;

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
                  <canvas id="previewer-canvas-${index}"></canvas>
                </div>`,
            );

            const canvas = document.getElementById(`previewer-canvas-${index}`);
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
                $(document).trigger('party-element:active', uuid);
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

        const triggerPartyElementAdd = (uuid, type, text = null) =>
            $(document).trigger('party-element:add', {
                uuid: uuid,
                eleType: type,
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
            $(document).trigger('party-element:remove', target.uuid);
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

            triggerPartyElementAdd(_uuid, target.eleType, target.text);
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
                left: data.offsetX,
                top: data.offsetY,
                width: data.width,
                height: data.height,
                fontSize: data.fontSize || data.height,
                padding: 5,
                fill: '#333333',
                color: '#333333',
            };

            const text = $.trim(data.text || data.eleType);

            switch (data.eleType) {
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

            fabricObject.eleType = data.eleType;
            fabricObject.uuid = _uuid;

            triggerPartyElementAdd(
                _uuid,
                data.eleType,
                data.text || fabricObject.text || data.eleType,
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
                .on('party-element:remove', (e, uuid) => {
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
                            offsetX: 238.34674585238713,
                            offsetY: 112.34266801044906,
                            width: 163.24307699999994,
                            height: 32.969557999999985,
                        },
                        {
                            page: 1,
                            eleType: 'signature_pad',
                            offsetX: 253.15437142614985,
                            offsetY: 218.27301736782994,
                            width: 111.6,
                            height: 22.599999999999998,
                        },
                        {
                            page: 2,
                            eleType: 'text',
                            offsetX: 185.7008572647063,
                            offsetY: 50.38610868284016,
                            width: 35.5,
                            height: 22.599999999999998,
                        },
                    ];

                    if (!blank(loadedObjectData)) {
                        canvasEditions.forEach((canvasEdition) => {
                            canvasEdition.clear();

                            loadedObjectData.forEach((objInfo, i) => {
                                const objPage = objInfo.page;
                                const totalPages = canvasEditions.length;

                                if (blank(objPage) || objPage > totalPages) {
                                    toast(
                                        'error',
                                        `Invalid element ${
                                            i + 1 + ' ' + objInfo.eleType
                                        } position on page ${objPage} while total pages are ${totalPages}!`,
                                    );
                                    return;
                                }

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
        });
    </script>
@endpushonce

@include('esign::documents.modals.signing-modal')
