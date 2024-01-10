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
        pdfDoc.getPage(1).then(function(page) {
            const scale = 1.5;
            const viewport = page.getViewport({ scale });

            const canvas = viewer;
            const context = canvas.getContext("2d");
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: context,
                viewport: viewport
            };

            page.render(renderContext);
        });
    });
}
