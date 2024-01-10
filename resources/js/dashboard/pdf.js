import * as pdfjsLib from "pdfjs-dist";
import * as pdfjsWorker from "pdfjs-dist/build/pdf.worker.mjs";

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorker;

$(() => {
    const pdfViewer = $("#pdfViewer");
    const url = pdfViewer.data("url");

    if (url) {
        loadPDF(url, pdfViewer);
    }
});

export async function loadPDF(url, viewer) {
    pdfjsLib.getDocument(url).promise.then(function(pdfDoc) {
        const numPages = pdfDoc.numPages;

        for (let pageNum = 1; pageNum <= numPages; pageNum++) {
            pdfDoc.getPage(pageNum).then(function(page) {
                const viewport = page.getViewport({scale: 1.5});
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');

                canvas.height = viewport.height;
                canvas.width = viewport.width;

                viewer.append(canvas);

                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };

                page.render(renderContext);
            });
        }
    });
}
