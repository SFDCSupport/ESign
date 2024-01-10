import { _ } from "./../_";
import * as FilePond from "filepond";
import { loadPDF } from "./pdf";
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
                    _filepondProcess(fieldName, file, metadata, load, error, progress, abort, transfer, options, { id: $("meta[name=\"document-id\"]").attr("content") });
                } else {
                    $(document).on("document:created", (e, data) => {
                        _filepondProcess(
                            fieldName,
                            file,
                            metadata,
                            load,
                            error,
                            progress,
                            abort,
                            transfer,
                            options,
                            data
                        );
                    }).on("document:cancelled", abort)
                        .trigger("document:creation", {
                            creationMode: "idOnly"
                        });
                }
            },
            headers: {
                "X-CSRF-TOKEN": _().getCSRFToken()
            }
        }
    });

    $(document).on("filepond:show-uploader", () => {
        $('input.filepond--browser[name="document"]').click();
    });
});

const _filepondProcess = (fieldName, file, metadata, load, error, progress, abort, transfer, options, data) => {
    const formData = new FormData();
    formData.append("_token", _().getCSRFToken());
    formData.append(fieldName, file, file.name);
    formData.append("id", data.id || null);
    formData.append("type", "document");

    const request = new XMLHttpRequest();
    request.open("POST", "/esign/upload/document");

    request.upload.onprogress = (e) => {
        progress(e.lengthComputable, e.loaded, e.total);
    };

    request.onload = () => {
        if (request.status >= 200 && request.status < 300) {
            if (data.redirectUrl) {
                window.location.assign(data.redirectUrl);
            } else {
                const url = request.responseText;
                const pdfViewer = $("#pdfViewer");

                loadPDF(url, pdfViewer);
                pdfViewer.data("url", url);
                docUploader.remove();
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
