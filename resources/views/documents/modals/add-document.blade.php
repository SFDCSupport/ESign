<x-esign::modal :canClose="true" id="addDocumentModal" :title="__('esign.label.document_add_modal_title')">
    <x-slot:body>
        <form
            id="createDocumentForm"
            method="POST"
            action="{{ route('esign.documents.store') }}"
        >
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
                    id="document-name"
                    name="title"
                    placeholder="{{ __('esign::label.document_name_placeholder') }}"
                />
            </div>
        </form>
    </x-slot:body>

    <x-slot:footer>
        <div class="Submissions-btns-grp w-100">
            <button
                type="button"
                id="saveDocumentBtn"
                class="btn btn-sm btn-dark add-part-btn"
            >
                {{ __('esign::label.document_add_modal_submit') }}
            </button>
        </div>
    </x-slot:footer>
</x-esign::modal>
