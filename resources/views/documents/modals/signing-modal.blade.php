<x-esign::modal title="" id="signing" size="modal-lg" role="signingModal">
    <nav>
        <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
            <button
                class="nav-link active ms-3"
                id="nav-home-tab"
                data-bs-toggle="tab"
                data-bs-target="#nav-home"
                type="button"
                role="tab"
                aria-controls="nav-home"
                aria-selected="true"
            >
                <i class="fa fa-pen-nib"></i>
                &nbsp; Draw Freehand
            </button>
            <button
                class="nav-link"
                id="nav-profile-tab"
                data-bs-toggle="tab"
                data-bs-target="#nav-profile"
                type="button"
                role="tab"
                aria-controls="nav-profile"
                aria-selected="false"
            >
                <i class="fa fa-font"></i>
                &nbsp; Enter Text
            </button>
        </div>
    </nav>
    <div class="tab-content p-2" id="nav-tabContent">
        <div
            class="tab-pane fade active show"
            id="nav-home"
            role="tabpanel"
            aria-labelledby="nav-home-tab"
        >
            First Tab
        </div>
        <div
            class="tab-pane fade"
            id="nav-profile"
            role="tabpanel"
            aria-labelledby="nav-profile-tab"
        >
            <label>
                <input
                    type="text"
                    class="form-control form-control-lg"
                    placeholder="{{ __('esign::label.my_signature') }}"
                />
            </label>
        </div>
    </div>

    <x-slot name="footer">
        <button
            type="button"
            class="btn btn-outline-secondary bootbox-cancel"
            data-bs-dismiss="modal"
        >
            {{ __('esign::label.close') }}
        </button>
        <button type="button" class="btn btn-primary">
            <i class="fa fa-check-circle"></i>
            {{ __('esign::label.create') }}
        </button>
    </x-slot>
</x-esign::modal>
