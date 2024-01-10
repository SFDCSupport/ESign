<x-esign::layout-app :title="__('esign::label.submissions')">
    <section class="grey-bg-section border-top">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div
                        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-2 mb-2 border-bottom"
                    >
                        <h1 class="h2">Partner Portal Guide</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                >
                                    <i class="fas fa-link"></i>
                                    &nbsp; Copy Link
                                </button>
                            </div>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 me-2"
                            >
                                <i class="fas fa-archive"></i>
                                &nbsp; Archive
                            </button>

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 me-2"
                            >
                                <i class="fa fa-copy"></i>
                                &nbsp; Clone
                            </button>

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 me-2"
                            >
                                <i class="fa fa-edit"></i>
                                &nbsp; Edit
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div
                        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-2 mb-2 align-items-center"
                    >
                        <h4 class="h4 mb-0">Submissions</h4>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <div class="btn-group me-2">
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                >
                                    <i class="fas fa-download"></i>
                                    &nbsp; Export
                                </button>
                            </div>
                            <button
                                type="button"
                                class="btn btn-secondary d-flex align-items-center gap-1 me-2"
                                data-bs-toggle="modal"
                                data-bs-target="#sendrecipientModal"
                            >
                                <i class="fas fa-plus"></i>
                                &nbsp; Add Recipients
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12 mb-3">
                    <div class="card document-template-cards">
                        <div class="card-body p-1">
                            <div
                                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-2 mb-2"
                            >
                                <div class="btn-toolbar align-items-center">
                                    <a
                                        href="#"
                                        class="btn btn-sm btn-success me-2"
                                    >
                                        Send
                                    </a>
                                    <span
                                        class="text-lg break-all flex items-center"
                                    >
                                        rahulkt07@gmail.com
                                    </span>
                                </div>
                                <div class="btn-toolbar mb-2 mb-md-0">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary me-2"
                                    >
                                        <i class="fas fa-link"></i>
                                        &nbsp; Copy Link
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary me-2"
                                    >
                                        <i class="fas fa-eye"></i>
                                        &nbsp; View
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

                <div class="col-md-12 col-sm-12 mb-3">
                    <div class="card document-template-cards">
                        <div class="card-body p-1">
                            <div
                                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-2 mb-2"
                            >
                                <div class="btn-toolbar align-items-center">
                                    <a
                                        href="#"
                                        class="btn btn-sm btn-success me-2"
                                    >
                                        Send
                                    </a>
                                    <span
                                        class="text-lg break-all flex items-center"
                                    >
                                        m.john@gmail.com
                                    </span>
                                </div>
                                <div class="btn-toolbar mb-2 mb-md-0">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary me-2"
                                    >
                                        <i class="fas fa-link"></i>
                                        &nbsp; Copy Link
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary me-2"
                                    >
                                        <i class="fas fa-eye"></i>
                                        &nbsp; View
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
            </div>
        </div>
    </section>

    @include('esign::documents.partials.send')
</x-esign::layout-app>
