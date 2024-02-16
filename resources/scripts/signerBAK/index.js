import axios from 'axios';
import SignPDF from './SignPDF';
import fs from 'node:fs';

async function main() {
    const args = process.argv.slice(2);

    if (args.length !== 2) {
        console.error('Usage: node signPdf.js <pdfFileUrl> <certificateFileUrl>');
        return;
    }

    const pdfFileUrl = args[0];
    const certificateFileUrl = args[1];

    const [pdfResponse, certificateResponse] = await Promise.all([
        axios.get(pdfFileUrl, { responseType: 'arraybuffer' }),
        axios.get(certificateFileUrl, { responseType: 'arraybuffer' }),
    ]);

    const pdfBuffer = new SignPDF(pdfResponse.data, certificateResponse.data);

    const signedDocs = await pdfBuffer.signPDF();

    const randomNumber = Math.floor(Math.random() * 5000);
    const pdfName = `nodejs/sources/exports/exported_file_${randomNumber}.pdf`;

    fs.writeFileSync(pdfName, signedDocs);
    console.log(`New Signed PDF created called: ${pdfName}`);
}

main();
