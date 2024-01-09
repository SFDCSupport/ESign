import * as FilePond from 'filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import { getCSRFToken } from "./_helpers";

FilePond.registerPlugin(FilePondPluginFileValidateSize);
FilePond.registerPlugin(FilePondPluginFileValidateType);

FilePond.create(document.getElementById('filepond'), {
    acceptedFileTypes: ['application/pdf'],
    allowReplace: false,
    allowRevert: false,
    allowRemove: false,
    maxFiles: 1,
    server: {
        process: './esign/upload/document',
        headers: {
            'X-CSRF-TOKEN': getCSRFToken(),
        },
    }
});
