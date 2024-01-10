<div
    class="modal fade"
    id="sendRecipientModal"
    tabindex="-1"
    aria-labelledby="exampleModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="sendRecipientModalLabel">
                    Add New Recipients
                </h1>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>
            <div class="modal-body">
                <form
                    method="POST"
                    action="{{ route('esign.documents.signers.store', $document) }}"
                >
                    @csrf
                    <input
                        type="hidden"
                        name="documentId"
                        value="{{ $document->id }}"
                    />
                    <div class="mb-3">
                        <label for="recipient-name" class="col-form-label">
                            Add Emails
                        </label>
                        <textarea
                            class="form-control"
                            id="exampleFormControlTextarea1"
                            rows="3"
                            placeholder="Type Emails Here..."
                        ></textarea>
                    </div>
                    <div class="form-check mb-2">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            value=""
                            id="flexCheckDefault"
                        />
                        <label class="form-check-label" for="flexCheckDefault">
                            Send Emails
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="Submissions-btns-grp w-100">
                    <button
                        type="button"
                        class="btn btn-sm btn-dark add-part-btn"
                    >
                        Add Participant
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
