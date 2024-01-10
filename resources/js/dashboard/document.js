import { _ } from "./../_";

const $ = _().$;

$(() => {
    const createDocumentForm = $("#createDocumentForm");
    const creationModeInput = createDocumentForm.find("input[name=\"creationMode\"]");

    $(document).on("click", "#saveDocumentBtn", () => {
        $.post(
            createDocumentForm.attr("action"),
            createDocumentForm.serialize()
        ).done((r) => {
            if (r.id && r.redirect) {
                $(document).trigger("document:created", {
                    id: r.id,
                    redirectUrl: r.redirect
                });
            }

            $("#addDocumentModal .btn-close").click();
        }).fail((x) => {

        });
    }).on("document:creation", (e, data) => {
        creationModeInput.val(data.creationMode);

        $("#createDocumentBtn").click();
    }).on("hidden.bs.modal", "#addDocumentModal", (e) => {
        creationModeInput.val("");

        if (e.relatedTarget !== null) {
            $(document).trigger("document:cancelled");
        }
    });
});
