import $ from 'jquery';
import * as pdfjsLib from "pdfjs-dist";
import * as pdfjsWorker from "pdfjs-dist/build/pdf.worker.mjs";
import interact from 'interactjs'
import { fabric } from "fabric";

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorker;

window.jQuery = window.$ = $;

$(function () {
    const form = document.getElementById('signersForm');
    const documentContainer = document.getElementById('documentContainer');
    const saveButton = document.getElementById('saveButton');
    let elementCounter = 1;

    function renderPDF(file) {
        const fileReader = new FileReader();

        fileReader.onload = function () {
            const typedarray = new Uint8Array(this.result);
            pdfjsLib.getDocument(typedarray).promise.then(function (pdfDocument) {
                const numPages = pdfDocument.numPages;

                for (let pageNum = 1; pageNum <= numPages; pageNum++) {
                    pdfDocument.getPage(pageNum).then(function (pdfPage) {
                        const viewport = pdfPage.getViewport({ scale: 1.5 });
                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');

                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        documentContainer.appendChild(canvas);

                        pdfPage.render({ canvasContext: context, viewport: viewport });
                    });
                }

                enableDraggableForElements();
            });
        };

        fileReader.readAsArrayBuffer(file);
    }

    function addSignerSection(signerIndex) {
        const signerContainer = document.getElementById('signerContainer');

        const signerSection = document.createElement('div');
        signerSection.classList.add('signer');

        signerSection.innerHTML = `
                    <label for="signer${signerIndex}">Signer ${signerIndex} Email:</label>
                    <input type="text" id="signer${signerIndex}" name="signers[${signerIndex}][email]" required>
                `;

        signerContainer.appendChild(signerSection);
    }

    function enableDraggableForElements() {
        const elementsContainer = document.getElementById('documentContainer');
        const sidebarElements = document.querySelectorAll('#sidebar .element');

        interact(elementsContainer).dropzone({
            accept: '.element',
            overlap: 0.75,

            ondragenter: function (event) {
                event.target.style.border = '2px dashed #333';
            },

            ondragleave: function (event) {
                event.target.style.border = 'none';
            },

            ondrop: function (event) {
                const type = event.relatedTarget.getAttribute('data-type');
                const x = event.dragEvent.clientX - elementsContainer.getBoundingClientRect().left;
                const y = event.dragEvent.clientY - elementsContainer.getBoundingClientRect().top;

                addDraggableElement(type, x, y);
            },
        });

        sidebarElements.forEach(element => {
            interact(element).draggable({
                inertia: true,
                modifiers: [
                    interact.modifiers.restrictRect({
                        restriction: 'parent',
                        endOnly: true,
                    }),
                ],
            });
        });
    }

    function addDraggableElement(type, x, y) {
        const element = document.createElement('div');
        element.classList.add('element');
        element.innerHTML = `${type} ${elementCounter}`;
        element.style.left = `${x}px`;
        element.style.top = `${y}px`;

        interact(element).draggable({
            inertia: true,
            modifiers: [
                interact.modifiers.restrictRect({
                    restriction: 'parent',
                    endOnly: true,
                }),
            ],
        });

        documentContainer.appendChild(element);
        elementCounter++;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(form);
        const jsonData = [];

        for (let signerIndex = 1; signerIndex <= formData.getAll('signers[][email]').length; signerIndex++) {
            const signerEmail = formData.get(`signers[${signerIndex}][email]`);
            const signerElements = [];

            document.querySelectorAll('.element').forEach(element => {
                const type = element.innerHTML.split(' ')[0]; // Extract type from innerHTML
                const posX = element.style.left;
                const posY = element.style.top;

                signerElements.push({
                    type: type,
                    pos_x: posX,
                    pos_y: posY,
                });
            });

            jsonData.push({
                email: signerEmail,
                elements: signerElements,
            });
        }

        console.log(jsonData);
    });

    document.getElementById('document').addEventListener('change', function () {
        const file = this.files[0];
        renderPDF(file);
    });

    saveButton.addEventListener('click', function () {
        form.dispatchEvent(new Event('submit'));
    });

    $('#addSigner').on('click', function(){
        const signers = $('#signers');
        const optionsLength = signers.find('option[value!=""]').length + 1;

        signers.append(`<option value="${optionsLength}">Signer ${optionsLength}</option>`)
    });
});
