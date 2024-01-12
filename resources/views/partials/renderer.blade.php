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
                            .on('mouse:down', function (e) {})
                            .on('object:scaling', (e) => {})
                            .on('object:scaled', function (e) {})
                            .on('text:changed', function (e) {});

                        canvasEditions.push(canvasEdition);
                    });
                }

                $(document).trigger('canvas:ready');
            });
        };

        const createFabricObject = (data, offsetX, offsetY) => {
            let fabricObject;
            const deleteIcon =
                "data:image/svg+xml,%3C%3Fxml version='1.0' encoding='utf-8'%3F%3E%3C!DOCTYPE svg PUBLIC '-//W3C//DTD SVG 1.1//EN' 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'%3E%3Csvg version='1.1' id='Ebene_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='595.275px' height='595.275px' viewBox='200 215 230 470' xml:space='preserve'%3E%3Ccircle style='fill:%23F44336;' cx='299.76' cy='439.067' r='218.516'/%3E%3Cg%3E%3Crect x='267.162' y='307.978' transform='matrix(0.7071 -0.7071 0.7071 0.7071 -222.6202 340.6915)' style='fill:white;' width='65.545' height='262.18'/%3E%3Crect x='266.988' y='308.153' transform='matrix(0.7071 0.7071 -0.7071 0.7071 398.3889 -83.3116)' style='fill:white;' width='65.544' height='262.179'/%3E%3C/g%3E%3C/svg%3E";
            const img = document.createElement('img');
            img.src = deleteIcon;

            fabric.Object.prototype.transparentCorners = false;
            fabric.Object.prototype.cornerColor = 'blue';
            fabric.Object.prototype.cornerStyle = 'circle';

            const commonStyles = {
                left: offsetX,
                top: offsetY,
                width: data.width,
                height: data.height,
                selectable: true,
                hasControls: true,
                hasBorders: true,
                cornerRadius: 20,
                strokeWidth: 4,
                stroke: '#333333',
                fill: '#fefefe',
                color: '#333333',
                hasRotatingPoint: false,
                centerTransform: true,
                originX: 'center',
                originY: 'center',
                lockUniScaling: true,
                transparentCorners: false,
                padding: 8,
            };

            switch (data.dataType) {
                case 'signature':
                    fabricObject = new fabric.Text('Signature', {
                        ...commonStyles,
                        fontSize: 16,
                        fill: '#fefefe',
                        backgroundColor: '#000000',
                    });
                    break;
                case 'text':
                    fabricObject = new fabric.IText(data.text, {
                        ...commonStyles,
                        fontSize: 16,
                        strokeWidth: 0,
                        fill: '#333333',
                        backgroundColor: '#fefefe',
                    });
                    break;
                default:
                    fabricObject = new fabric.Rect(commonStyles);
                    break;
            }

            return fabricObject;
        };

        const renderElements = (elements) => {};

        $(() => {
            const pdfViewer = $('#pdfViewer');
            const url = pdfViewer.data('url');

            if (url) {
                loadPDF(url, pdfViewer);
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
