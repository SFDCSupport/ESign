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
                    <div class="dark-bg-card p-2 py-3 rounded mb-3">
                        <label for="formControlInput1" class="col-form-label pt-1 pb-1">
                            {{ __('esign::label.nth_signer', ['nth' => ordinal(1)]) }}
                        </label>
                        <input
                            class="form-control"
                            id="formControlInput1"
                            placeholder="{{ __('esign::label.type_email_here') }}"
                        />
                    </div>
                    <div class="col-sm-12 mt-3 d-flex justify-content-between">
                       <div class="col"> 
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
                                id="preserve_order"
                            />
                            <label
                                class="form-check-label"
                                for="preserve_order"
                            >
                                {{ __('esign::label.preserve_order') }}
                            </label>
                        </div>
                    </div>
                        <div class="col text-end">
                            <div class="form-check">
                                <a class="editmessage-link" id="" href="#" aria-valuemax="" data-toggle="section" data-target="#mailSection">Edit Message</a>
                              </div>
                        </div>
                    </div>


                    <div class="col-sm-12">
            
                        <div class="dark-bg-card p-2 rounded mb-3 mt-3 d-none" id="mailSection">
                          <div class="row">
                            <div class="col-sm-12 mb-3">
                              <label for="firstparty" class="form-label mb-1">Subject</label>
                              <input class="form-control form-control" type="text" value="You are invited to submit a form" placeholder="Subject" aria-label="">
                            </div>
                  
                            <div class="col-sm-12 mb-3">
                              <label for="bodysubject" class="form-label mb-1">Body &nbsp;<i class="fa fa-info-circle"></i></label>
                              <textarea class="form-control" id="bodysubject" rows="9&quot;" placeholder="Body"></textarea>
                            </div>
                  
                          </div>
                  
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
