<div class="col-md-12 col-sm-12 mb-3">
    <div class="card document-template-cards">
        <div class="card-body p-1">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-2 mb-2"
            >
                <div class="btn-toolbar align-items-center">
                    <a href="#" class="btn btn-sm btn-success me-2">
                        {{ $signer->status }}
                    </a>
                    <span class="text-lg break-all flex items-center">
                        {{ $signer->email }}
                    </span>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary me-2"
                    >
                        <i class="fas fa-link"></i>
                        {{ __('esign::label.copy_link') }}
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary me-2"
                    >
                        <i class="fas fa-eye"></i>
                        {{ __('esign::label.view') }}
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger me-2"
                    >
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
