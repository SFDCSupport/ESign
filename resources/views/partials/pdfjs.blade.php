@pushonce('js')
    <script src="{{ url('vendor/esign/js/pdf.js') }}?legacy"></script>
    <script src="{{ url('vendor/esign/js/signature_pad.umd.min.js') }}?3.0.0-beta.3"></script>
    <script>
        const loadPDF = (url, viewer) => {
            const pdfjsLib = window['pdfjs-dist/build/pdf'];
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                '/vendor/esign/js/pdf.worker.js?legacy';

            pdfjsLib.getDocument(url).promise.then((pdfDoc) => {
                const numPages = pdfDoc.numPages;

                for (let pageNum = 1; pageNum <= numPages; pageNum++) {
                    pdfDoc.getPage(pageNum).then((page) => {
                        const viewport = page.getViewport({ scale: 1.5 });
                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');

                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        viewer.append(canvas);

                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport,
                        };

                        page.render(renderContext);
                    });
                }
            });
        };

        $(() => {
            const pdfViewer = $('#pdfViewer');
            const url = pdfViewer.data('url');

            if (url) {
                loadPDF(url, pdfViewer);
            }
        });
    </script>
@endpushonce
