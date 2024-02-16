import {
    PDFDocument,
    PDFName,
    PDFNumber,
    PDFHexString,
    PDFString,
} from "pdf-lib";
import signer from "node-signpdf";

import PDFArrayCustom from "./PDFArrayCustom";

export default class SignPDF {
    constructor(pdfFile, certFile) {
        this.pdfDoc = pdfFile;
        this.certificate = certFile;
    }

    /**
     * @return Promise<Buffer>
     */
    async signPDF() {
        let newPDF = await this._addPlaceholder();
        newPDF = signer.sign(newPDF, this.certificate, {passphrase: "password"});

        return newPDF;
    }

    /**
     * @returns {Promise<Buffer>}
     */
    async _addPlaceholder() {
        const loadedPdf = await PDFDocument.load(this.pdfDoc);
        const ByteRange = PDFArrayCustom.withContext(loadedPdf.context);
        const DEFAULT_BYTE_RANGE_PLACEHOLDER = '**********';
        const SIGNATURE_LENGTH = 3322;
        const pages = loadedPdf.getPages();

        ByteRange.push(PDFNumber.of(0));
        ByteRange.push(PDFName.of(DEFAULT_BYTE_RANGE_PLACEHOLDER));
        ByteRange.push(PDFName.of(DEFAULT_BYTE_RANGE_PLACEHOLDER));
        ByteRange.push(PDFName.of(DEFAULT_BYTE_RANGE_PLACEHOLDER));

        const signatureDict = loadedPdf.context.obj({
            Type: 'Sig',
            Filter: 'Adobe.PPKLite',
            SubFilter: 'adbe.pkcs7.detached',
            ByteRange,
            Contents: PDFHexString.of('A'.repeat(SIGNATURE_LENGTH)),
            Reason: PDFString.of('We need your signature for reasons...'),
            M: PDFString.fromDate(new Date()),
        });

        const signatureDictRef = loadedPdf.context.register(signatureDict);

        const widgetDict = loadedPdf.context.obj({
            Type: 'Annot',
            Subtype: 'Widget',
            FT: 'Sig',
            Rect: [0, 0, 0, 0],
            V: signatureDictRef,
            T: PDFString.of('test signature'),
            F: 4,
            P: pages[0].ref,
        });

        const widgetDictRef = loadedPdf.context.register(widgetDict);

        pages[0].node.set(PDFName.of('Annots'), loadedPdf.context.obj([widgetDictRef]));

        loadedPdf.catalog.set(
            PDFName.of('AcroForm'),
            loadedPdf.context.obj({
                SigFlags: 3,
                Fields: [widgetDictRef],
            })
        );

        const pdfBytes = await loadedPdf.save({ useObjectStreams: false });

        return SignPDF.unit8ToBuffer(pdfBytes);
    }

    /**
     * @param {Uint8Array} unit8
     */
    static unit8ToBuffer(unit8) {
        let buf = Buffer.alloc(unit8.byteLength);
        const view = new Uint8Array(unit8);

        for (let i = 0; i < buf.length; ++i) {
            buf[i] = view[i];
        }
        return buf;
    }
}
