#!/usr/bin/env node
"use strict";

import { CharCodes, PDFArray, PDFDocument, PDFHexString, PDFName, PDFNumber, PDFString } from "pdf-lib";
import signer from "node-signpdf";
import * as fs from "fs-extra";
import * as yargs from "yargs";

const argv = yargs
    .option("input", {
        alias: "i",
        desc: "PDF absolute path"
    })
    .option("output", {
        alias: "o",
        desc: "Output absolute path"
    })
    .option("cert", {
        alias: "c",
        desc: "Cert path"
    })
    .option("password", {
        alias: "p",
        desc: "Password"
    })
    .option("info", {
        desc: "Additional info: reason;text;"
    })
    .demandOptions(["input", "output", "cert", "password"])
    .help()
    .argv;

class PDFArrayCustom extends PDFArray {
    static withContext(context) {
        return new PDFArrayCustom(context);
    }

    clone(context) {
        const clone = PDFArrayCustom.withContext(context || this.context);
        for (let idx = 0, len = this.size(); idx < len; idx++) {
            clone.push(this.array[idx]);
        }
        return clone;
    }

    toString() {
        let arrayString = "[";
        for (let idx = 0, len = this.size(); idx < len; idx++) {
            arrayString += this.get(idx).toString();
            if (idx < len - 1) arrayString += " ";
        }
        arrayString += "]";
        return arrayString;
    }

    sizeInBytes() {
        let size = 2;
        for (let idx = 0, len = this.size(); idx < len; idx++) {
            size += this.get(idx).sizeInBytes();
            if (idx < len - 1) size += 1;
        }
        return size;
    }

    copyBytesInto(buffer, offset) {
        const initialOffset = offset;

        buffer[offset++] = CharCodes.LeftSquareBracket;
        for (let idx = 0, len = this.size(); idx < len; idx++) {
            offset += this.get(idx).copyBytesInto(buffer, offset);
            if (idx < len - 1) buffer[offset++] = CharCodes.Space;
        }
        buffer[offset++] = CharCodes.RightSquareBracket;

        return offset - initialOffset;
    }
}

class SignPDF {
    constructor(pdfFile, certFile, password, info) {
        this.pdfDoc = fs.readFileSync(pdfFile);
        this.certificate = fs.readFileSync(certFile);
        this.password = password;
        this.info = info;
    }

    /**
     * @return Promise<Buffer>
     */
    async signPDF() {
        let newPDF = await this._addPlaceholder();
        newPDF = signer.sign(newPDF, this.certificate, { passphrase: this.password });

        return newPDF;
    }

    /**
     * @returns {Promise<Buffer>}
     */
    async _addPlaceholder() {
        const loadedPdf = await PDFDocument.load(this.pdfDoc);
        const ByteRange = PDFArrayCustom.withContext(loadedPdf.context);
        const DEFAULT_BYTE_RANGE_PLACEHOLDER = "**********";
        const SIGNATURE_LENGTH = 3322;
        const pages = loadedPdf.getPages();

        ByteRange.push(PDFNumber.of(0));
        ByteRange.push(PDFName.of(DEFAULT_BYTE_RANGE_PLACEHOLDER));
        ByteRange.push(PDFName.of(DEFAULT_BYTE_RANGE_PLACEHOLDER));
        ByteRange.push(PDFName.of(DEFAULT_BYTE_RANGE_PLACEHOLDER));

        const signatureDict = loadedPdf.context.obj({
            Type: "Sig",
            Filter: "Adobe.PPKLite",
            SubFilter: "adbe.pkcs7.detached",
            ByteRange,
            Contents: PDFHexString.of("A".repeat(SIGNATURE_LENGTH)),
            Reason: PDFString.of(this.info.reason ?? "We need your signature for reasons..."),
            M: PDFString.fromDate(new Date())
        });

        const signatureDictRef = loadedPdf.context.register(signatureDict);

        const widgetDict = loadedPdf.context.obj({
            Type: "Annot",
            Subtype: "Widget",
            FT: "Sig",
            Rect: [0, 0, 0, 0],
            V: signatureDictRef,
            T: PDFString.of(this.info.text ?? "test signature"),
            F: 4,
            P: pages[0].ref
        });

        const widgetDictRef = loadedPdf.context.register(widgetDict);

        pages[0].node.set(PDFName.of("Annots"), loadedPdf.context.obj([widgetDictRef]));

        loadedPdf.catalog.set(
            PDFName.of("AcroForm"),
            loadedPdf.context.obj({
                SigFlags: 3,
                Fields: [widgetDictRef]
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

const main = async () => {
    const { input, cert, password, info } = argv;
    const PDFBuffer = new SignPDF(input, cert, password, info);
    const signedDocs = await PDFBuffer.signPDF();

    fs.outputFileSync(argv.output, signedDocs);
};

main();
