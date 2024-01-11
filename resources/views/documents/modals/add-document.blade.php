<template id="addDocumentModalTemplate">
    <form id="createDocumentForm">
        @csrf

        <input type="hidden" name="mode" value="create" />
        <input type="hidden" name="creationMode" />

        <div class="mb-3">
            <label for="document-name" class="col-form-label">
                {{ __('esign::label.document_name') }}
            </label>
            <input
                type="text"
                class="form-control"
                id="documentName"
                name="title" required
                placeholder="{{ __('esign::label.document_name_placeholder') }}"
            />
        </div>
    </form>
</template>

@pushonce('js')
    <script>
        $(() => {
            let addDocumentBootboxInstance;

            const addDocumentBootbox = (callback) => bootbox.dialog({
                title: '{{ __('esign::label.document_add_modal_title') }}',
                message: $("#addDocumentModalTemplate").html(),
                closeButton: false,
                backdrop: "static",
                size: "lg",
                buttons: {
                    cancel: {
                        label: '{{ __('esign::label.close') }}',
                        className: "light-btn",
                        callback: callback(false)
                    },
                    ok: {
                        label: '{{ __('esign::label.submit') }}',
                        className: "btn-primary",
                        callback: () => {
                            const form = $("#createDocumentForm");

                            form.validate({
                                debug: false,
                                rules: {
                                    title: {
                                        required: true
                                    }
                                }
                            });

                            if (!form.valid()) {
                                return false;
                            }

                            setTimeout(() => {
                                $(document).trigger("loader:show");
                            }, 0);

                            $.post(
                                '{{ route('esign.documents.store') }}',
                                form.serialize()
                            ).done((r) => {
                                if (r.redirect) {
                                    callback(r);
                                }

                                return false;
                            }).fail((x) => {
                                callback(false);
                            });

                            return false;
                        }
                    }
                }
            });

            $(document).on("modal:add-document:show", (e, data) => {
                (addDocumentBootboxInstance = addDocumentBootbox((data.callback))).modal("show");
            }).on("modal:add-document:hide", () => {
                addDocumentBootboxInstance().modal("hide");
            });
        });
    </script>
@endpushonce
