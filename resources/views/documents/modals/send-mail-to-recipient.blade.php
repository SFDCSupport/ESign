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
                    {{ __('esign::label.add_signers') }}
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
                        <label for="formControlInput1" class="col-form-label">
                            {{ __('esign::label.nth_signer', ['nth' => ordinal(1)]) }}
                        </label>
                        <input
                            class="form-control"
                            id="formControlInput1"
                            placeholder="{{ __('esign::label.type_email_here') }}"
                        />
                    </div>
                    <div>
                        <div class="form-check mb-2">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                value=""
                                id="flexCheckDefault"
                            />
                            <label
                                class="form-check-label"
                                for="flexCheckDefault"
                            >
                                {{ __('esign::label.send_emails') }}
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                value=""
                                id="flexCheckDefault"
                            />
                            <label
                                class="form-check-label"
                                for="flexCheckDefault"
                            >
                                {{ __('esign::label.preserve_order') }}
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="Submissions-btns-grp w-100">
                    <button
                        type="button"
                        class="btn btn-sm btn-dark add-part-btn"
                    >
                        {{ __('esign::label.send') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
