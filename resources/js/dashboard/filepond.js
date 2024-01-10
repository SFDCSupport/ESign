import { _ } from "./../_";
import * as FilePond from "filepond";
import FilePondPluginPdfPreview from "filepond-plugin-pdf-preview";
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
import FilePondPluginFileValidateSize from "filepond-plugin-file-validate-size";

FilePond.registerPlugin(FilePondPluginPdfPreview);
FilePond.registerPlugin(FilePondPluginFileValidateSize);
FilePond.registerPlugin(FilePondPluginFileValidateType);

const $ = _().$;

$(() => {
    const docUploader = $("#document");

    FilePond.create(docUploader.get(0), {
        allowPdfPreview: true,
        server: {
            process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                if (docUploader.data("page") === "inner") {
                    _filepondProcess(fieldName, file, metadata, load, error, progress, abort, transfer, options, $("meta[name=\"document-id\"]").attr("content"));
                } else {
                    $(document).on("document:created", (e, id, redirectUrl) => {
                        _filepondProcess(fieldName, file, metadata, load, error, progress, abort, transfer, options, id, redirectUrl);
                    }).on("document:cancelled", abort)
                        .trigger("document:creation", ["idOnly"]);
                }
            },
            headers: {
                "X-CSRF-TOKEN": _().getCSRFToken()
            }
        }
    });
});

const _filepondProcess = (fieldName, file, metadata, load, error, progress, abort, transfer, options, id, redirectUrl) => {
    const formData = new FormData();
    formData.append("_token", _().getCSRFToken());
    formData.append(fieldName, file, file.name);
    formData.append("id", id);

    const request = new XMLHttpRequest();
    request.open("POST", "/esign/upload/document");

    request.upload.onprogress = (e) => {
        progress(e.lengthComputable, e.loaded, e.total);
    };

    request.onload = () => {
        if (request.status >= 200 && request.status < 300) {
            if(redirectUrl) {
                window.location.assign(redirectUrl);
            }else{
                load(request.responseText);
            }
        } else {
            error("oh no");
        }
    };

    request.send(formData);

    return {
        abort: () => {
            request.abort();
            abort();
        }
    };
};
