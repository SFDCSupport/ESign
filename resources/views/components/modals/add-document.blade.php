<div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModal"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="addDocumentModalLabel">
                    {{ __('esign.label.document_add_modal_title') }}
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('esign::label.close') }}"></button>
            </div>
            <div class="modal-body">
                <form id="createDocumentForm" method="POST" action="{{ route('esign.documents.store') }}">
                    @csrf
                    <input type="hidden" name="mode" value="create"/>
                    <div class="mb-3">
                        <label for="document-name" class="col-form-label">
                            {{ __('esign::label.document_name') }}
                        </label>
                        <input type="text" class="form-control" id="document-name" name="title"
                               placeholder="{{ __('esign::label.document_name_placeholder') }}">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="Submissions-btns-grp w-100">
                    <button type="button" id="createDocumentBtn" class="btn btn-sm btn-dark add-part-btn">
                        {{ __('esign::label.document_add_modal_submit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
