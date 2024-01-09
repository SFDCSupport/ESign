import {_} from './../_';
import * as FilePond from 'filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';

FilePond.registerPlugin(FilePondPluginFileValidateSize);
FilePond.registerPlugin(FilePondPluginFileValidateType);

FilePond.create(document.querySelectorAll('.filepond'), {
    allowReplace: false,
    allowRevert: false,
    allowRemove: false,
    server: {
        process: './esign/upload/document',
        headers: {
            'X-CSRF-TOKEN': _().getCSRFToken(),
        },
    }
});
