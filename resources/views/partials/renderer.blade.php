@pushonce('js')
    <script src="{{ url('vendor/esign/js/pdf.js') }}?legacy"></script>
    <script src="{{ url('/vendor/esign/js/fabric.min.js') }}?4.6.0"></script>
    <script src="{{ url('vendor/esign/js/signature_pad.umd.min.js') }}?3.0.0-beta.3"></script>
    <script>
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

                            if (viewport.width > viewer[0].clientWidth - 40) {
                                viewport = page.getViewport({ scale: 1 });
                                scale =
                                    (viewer[0].clientWidth - 40) /
                                    viewport.width;
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

                            renderTask.promise.then(
                                () => {
                                    const msg = `Page ${pageNum} successfully rendered.`;
                                    toast('info', msg);
                                    console.log(msg);
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
                                    const dataType = draggedData.dataType;
                                    const text = draggedData.text;
                                    const height = draggedData.height || 50;
                                    const width = draggedData.width || 100;

                                    const fabricObject = createFabricObject(
                                        { dataType, text, height, width },
                                        offsetX,
                                        offsetY,
                                    );

                                    canvasEdition.add(fabricObject);
                                },
                            );

                            canvasEdition
                                .on('mouse:move', function (e) {})
                                .on('mouse:down:before', function (e) {})
                                .on('mouse:down', function (e) {
                                    if (e.target) {
                                        const dataType = e.target.dataType;

                                        switch (dataType) {
                                            case 'signature_pad':
                                                console.log('signature pad');
                                                break;
                                            case 'text':
                                                const textObject = e.target;
                                                const inputBox =
                                                    document.createElement(
                                                        'input',
                                                    );
                                                inputBox.type = 'text';
                                                inputBox.value =
                                                    textObject.text;
                                                inputBox.style.position =
                                                    'absolute';
                                                inputBox.style.left =
                                                    textObject.left + 'px';
                                                inputBox.style.top =
                                                    textObject.top + 'px';
                                                inputBox.style.width =
                                                    textObject.width + 'px';
                                                inputBox.style.height =
                                                    textObject.height + 'px';

                                                canvasEdition.remove(
                                                    textObject,
                                                );
                                                document.body.appendChild(
                                                    inputBox,
                                                );

                                                inputBox.addEventListener(
                                                    'blur',
                                                    () => {
                                                        textObject.set(
                                                            'text',
                                                            inputBox.value,
                                                        );
                                                        canvasEdition.add(
                                                            textObject,
                                                        );
                                                        document.body.removeChild(
                                                            inputBox,
                                                        );
                                                        canvasEdition.renderAll();
                                                    },
                                                );

                                                break;
                                            default:
                                                console.warn(
                                                    'Unknown data type:',
                                                    dataType,
                                                );
                                                break;
                                        }
                                    }
                                })
                                .on('object:scaling', (e) => {})
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

        const loadObjectsFromData = (canvasEdition, objectData) => {
            objectData.forEach((objInfo) => {
                console.log(objInfo.page, canvasEditions.pageIndex + 1);
                if (objInfo.page === canvasEdition.pageIndex + 1) {
                    let newObj;
                    const defaultObj = {
                        left: objInfo.x,
                        top: objInfo.y,
                        width: objInfo.width,
                        height: objInfo.height,
                        fill: 'red',
                        selectable: false,
                        lockScaling: true,
                        lockMovement: true,
                    };

                    switch (objInfo.type) {
                        case 'text':
                            newObj = new fabric.Rect({
                                ...defaultObj,
                                fill: 'blue',
                            });
                            break;
                        case 'signature_pad':
                            newObj = new fabric.Rect({
                                ...defaultObj,
                                fill: 'pink',
                            });
                            break;
                        default:
                            console.warn('Unknown data type:', objInfo.type);
                            return;
                    }

                    newObj.dataType = objInfo.type;
                    canvasEdition.add(newObj);
                }
            });
        };

        const saveBtnAction = () => {
            canvasEditions.forEach((canvasEdition, pageIndex) => {
                canvasEdition.forEachObject((obj) => {
                    console.log('Object Info:', {
                        page: canvasEdition.pageIndex + 1,
                        type: obj.dataType,
                        x: obj.left,
                        y: obj.top,
                        width: obj.width,
                        height: obj.height,
                    });
                });
            });
        };

        const createFabricObject = (data, offsetX, offsetY) => {
            let fabricObject;
            const deleteIcon =
                "data:image/svg+xml,%3C%3Fxml version='1.0' encoding='utf-8'%3F%3E%3C!DOCTYPE svg PUBLIC '-//W3C//DTD SVG 1.1//EN' 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'%3E%3Csvg version='1.1' id='Ebene_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='595.275px' height='595.275px' viewBox='200 215 230 470' xml:space='preserve'%3E%3Ccircle style='fill:%23F44336;' cx='299.76' cy='439.067' r='218.516'/%3E%3Cg%3E%3Crect x='267.162' y='307.978' transform='matrix(0.7071 -0.7071 0.7071 0.7071 -222.6202 340.6915)' style='fill:white;' width='65.545' height='262.18'/%3E%3Crect x='266.988' y='308.153' transform='matrix(0.7071 0.7071 -0.7071 0.7071 398.3889 -83.3116)' style='fill:white;' width='65.544' height='262.179'/%3E%3C/g%3E%3C/svg%3E";
            const cloneIcon =
                "data:image/svg+xml,%3C%3Fxml version='1.0' encoding='iso-8859-1'%3F%3E%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 55.699 55.699' width='100px' height='100px' xml:space='preserve'%3E%3Cpath style='fill:%23010002;' d='M51.51,18.001c-0.006-0.085-0.022-0.167-0.05-0.248c-0.012-0.034-0.02-0.067-0.035-0.1 c-0.049-0.106-0.109-0.206-0.194-0.291v-0.001l0,0c0,0-0.001-0.001-0.001-0.002L34.161,0.293c-0.086-0.087-0.188-0.148-0.295-0.197 c-0.027-0.013-0.057-0.02-0.086-0.03c-0.086-0.029-0.174-0.048-0.265-0.053C33.494,0.011,33.475,0,33.453,0H22.177 c-3.678,0-6.669,2.992-6.669,6.67v1.674h-4.663c-3.678,0-6.67,2.992-6.67,6.67V49.03c0,3.678,2.992,6.669,6.67,6.669h22.677 c3.677,0,6.669-2.991,6.669-6.669v-1.675h4.664c3.678,0,6.669-2.991,6.669-6.669V18.069C51.524,18.045,51.512,18.025,51.51,18.001z M34.454,3.414l13.655,13.655h-8.985c-2.575,0-4.67-2.095-4.67-4.67V3.414z M38.191,49.029c0,2.574-2.095,4.669-4.669,4.669H10.845 c-2.575,0-4.67-2.095-4.67-4.669V15.014c0-2.575,2.095-4.67,4.67-4.67h5.663h4.614v10.399c0,3.678,2.991,6.669,6.668,6.669h10.4 v18.942L38.191,49.029L38.191,49.029z M36.777,25.412h-8.986c-2.574,0-4.668-2.094-4.668-4.669v-8.985L36.777,25.412z M44.855,45.355h-4.664V26.412c0-0.023-0.012-0.044-0.014-0.067c-0.006-0.085-0.021-0.167-0.049-0.249 c-0.012-0.033-0.021-0.066-0.036-0.1c-0.048-0.105-0.109-0.205-0.194-0.29l0,0l0,0c0-0.001-0.001-0.002-0.001-0.002L22.829,8.637 c-0.087-0.086-0.188-0.147-0.295-0.196c-0.029-0.013-0.058-0.021-0.088-0.031c-0.086-0.03-0.172-0.048-0.263-0.053 c-0.021-0.002-0.04-0.013-0.062-0.013h-4.614V6.67c0-2.575,2.095-4.67,4.669-4.67h10.277v10.4c0,3.678,2.992,6.67,6.67,6.67h10.399 v21.616C49.524,43.26,47.429,45.355,44.855,45.355z'/%3E%3C/svg%3E%0A";

            const deleteImg = document.createElement('img');
            deleteImg.src = deleteIcon;

            const cloneImg = document.createElement('img');
            cloneImg.src = cloneIcon;

            fabric.Object.prototype.transparentCorners = false;
            fabric.Object.prototype.cornerColor = 'blue';
            fabric.Object.prototype.cornerStyle = 'circle';

            const commonStyles = {
                left: offsetX,
                top: offsetY,
                originX: 'center',
                originY: 'center',
                textAlign: 'center',
                width: data.width,
                height: data.height,
                selectable: true,
                hasControls: true,
                hasBorders: true,
                hasRotatingPoint: false,
                centerTransform: true,
                lockUniScaling: true,
                transparentCorners: false,
                objectCaching: false,
                cornerRadius: 20,
                strokeWidth: 4,
                padding: 8,
                stroke: '#333333',
                fill: '#fefefe',
                color: '#333333',
                backgroundColor: '#fee7e7',
            };

            switch (data.dataType) {
                case 'signature_pad':
                    fabricObject = new fabric.Text('Signature', {
                        ...commonStyles,
                        fontSize: 16,
                        strokeWidth: 0,
                        color: '#ffffff',
                        backgroundColor: '#000000',
                    });
                    break;
                default:
                    fabricObject = new fabric.IText(data.text, {
                        ...commonStyles,
                        fontSize: 16,
                        strokeWidth: 0,
                        fill: '#333333',
                    });
                    break;
            }

            function renderIcon(icon) {
                return function renderIcon(
                    ctx,
                    left,
                    top,
                    styleOverride,
                    fabricObject,
                ) {
                    var size = this.cornerSize;
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
                    canvas.add(cloned);
                });
            }

            fabric.Object.prototype.controls.deleteControl = new fabric.Control(
                {
                    x: 0.5,
                    y: -0.5,
                    offsetY: -16,
                    offsetX: 0,
                    cursorStyle: 'pointer',
                    mouseUpHandler: deleteObject,
                    render: renderIcon(deleteImg),
                    cornerSize: 18,
                },
            );

            fabric.Object.prototype.controls.clone = new fabric.Control({
                x: 0.5,
                y: -0.5,
                offsetY: -16,
                offsetX: -20,
                cursorStyle: 'pointer',
                mouseUpHandler: cloneObject,
                render: renderIcon(cloneImg),
                cornerSize: 18,
            });

            fabricObject.dataType = data.dataType;

            return fabricObject;
        };

        $(() => {
            const pdfViewer = $('#pdfViewer');
            const url = pdfViewer.data('url');

            if (url) {
                loadPDF(url, pdfViewer);

                $(document).on('canvas:ready', () => {
                    const loadedObjectData = [
                        {
                            page: 1,
                            type: 'signature_pad',
                            x: 202,
                            y: 150,
                            width: 61.28,
                            height: 18.08,
                        },
                        {
                            page: 1,
                            type: 'text',
                            x: 295,
                            y: 320,
                            width: 204.4,
                            height: 60.02559999999999,
                        },
                        {
                            page: 1,
                            type: 'email',
                            x: 332,
                            y: 216,
                            width: 213.76000000000002,
                            height: 60.02559999999999,
                        },
                    ];

                    if (!blank(loadedObjectData)) {
                        canvasEditions.forEach((canvasEdition) => {
                            canvasEdition.clear();
                            loadObjectsFromData(
                                canvasEdition,
                                loadedObjectData,
                            );
                        });
                    }
                });
            }

            if (!getSignerId()) {
                $('.draggable').on('dragstart', function (e) {
                    const dataType = $(this).data('type');
                    const text = $(this).find('.text-xs').text();
                    const height = $(this).data('height') || 50;
                    const width = $(this).data('width') || 100;

                    const data = {
                        dataType,
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
