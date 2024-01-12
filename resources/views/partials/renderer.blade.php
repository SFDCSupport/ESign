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

                        canvasEditions.push(canvasEdition);
                    });
                }

                $(document).trigger('canvas:ready');
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
