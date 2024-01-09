<x-esign::layout-app :title="__('esign::label.dashboard')">
    @pushonce('css')
        <style>
            body {
                background-color: #f6f9fc;
            }

            .bd-placeholder-img {
                font-size: 1.125rem;
                text-anchor: middle;
                -webkit-user-select: none;
                -moz-user-select: none;
                user-select: none;
            }

            @media (min-width: 768px) {
                .bd-placeholder-img-lg {
                    font-size: 3.5rem;
                }
            }

            .b-example-divider {
                width: 100%;
                height: 3rem;
                background-color: rgba(0, 0, 0, .1);
                border: solid rgba(0, 0, 0, .15);
                border-width: 1px 0;
                box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
            }

            .b-example-vr {
                flex-shrink: 0;
                width: 1.5rem;
                height: 100vh;
            }

            .bi {
                vertical-align: -.125em;
                fill: currentColor;
            }

            .nav-scroller {
                position: relative;
                z-index: 2;
                height: 2.75rem;
                overflow-y: hidden;
            }

            .nav-scroller .nav {
                display: flex;
                flex-wrap: nowrap;
                padding-bottom: 1rem;
                margin-top: -1px;
                overflow-x: auto;
                text-align: center;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }

            .btn-bd-primary {
                --bd-violet-bg: #712cf9;
                --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

                --bs-btn-font-weight: 600;
                --bs-btn-color: var(--bs-white);
                --bs-btn-bg: var(--bd-violet-bg);
                --bs-btn-border-color: var(--bd-violet-bg);
                --bs-btn-hover-color: var(--bs-white);
                --bs-btn-hover-bg: #6528e0;
                --bs-btn-hover-border-color: #6528e0;
                --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
                --bs-btn-active-color: var(--bs-btn-hover-color);
                --bs-btn-active-bg: #5a23c8;
                --bs-btn-active-border-color: #5a23c8;
            }

            .bd-mode-toggle {
                z-index: 1500;
            }

            .bd-mode-toggle .dropdown-menu .active .bi {
                display: block !important;
            }
        </style>
    @endpushonce
    <section class="grey-bg-section border-top">
        <section class="mb-2">
            <div class="container">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-1 mb-0">
                    <h1 class="h2">Document Templates</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Upload</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                                data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                            <i class="fa fa-plus"></i> &nbsp; Create
                        </button>
                    </div>
                </div>
            </div>
        </section>
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    <div class="card document-template-cards">
                        <div class="card-body">
                            <h5 class="card-title">Partner Portal Guide</h5>
                            <p class="user-date text-secondary mb-1"><i class="fa fa-user"></i>&nbsp;
                                <span>Rahul Thakur</span></p>
                            <p class="user-date text-secondary mb-1"><i class="fas fa-calendar-alt"></i>&nbsp; <span>14 Dec 06:15 PM</span>
                            </p>

                            <div class="space-y">
                                <div class="space-y-inner">
                                    <a href="" class="text-secondary" title="Edit"><i class="fa fa-edit"></i></a>
                                    <a href="" class="text-secondary" title="Copy"><i class="fa fa-copy"></i></a>
                                    <a href="" class="text-secondary" title="Delete"><i class="fa fa-trash"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="card document-template-cards">
                        <div class="card-body">
                            <h5 class="card-title">Sample Document</h5>
                            <p class="user-date text-secondary mb-1"><i class="fa fa-user"></i>&nbsp;
                                <span>Rahul Thakur</span></p>
                            <p class="user-date text-secondary mb-1"><i class="fas fa-calendar-alt"></i>&nbsp; <span>14 Dec 06:15 PM</span>
                            </p>
                            <div class="space-y">
                                <div class="space-y-inner">
                                    <a href="" class="text-secondary" title="Edit"><i class="fa fa-edit"></i></a>
                                    <a href="" class="text-secondary" title="Copy"><i class="fa fa-copy"></i></a>
                                    <a href="" class="text-secondary" title="Delete"><i class="fa fa-trash"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-4">
                    <div class="card document-template-cards">
                        <div class="card-body">
                            <h5 class="card-title">Sample Document 2</h5>
                            <p class="user-date text-secondary mb-1"><i class="fa fa-user"></i>&nbsp;
                                <span>Rahul Thakur</span></p>
                            <p class="user-date text-secondary mb-1"><i class="fas fa-calendar-alt"></i>&nbsp; <span>14 Dec 06:15 PM</span>
                            </p>

                            <div class="space-y">
                                <div class="space-y-inner">
                                    <a href="" class="text-secondary" title="Edit"><i class="fa fa-edit"></i></a>
                                    <a href="" class="text-secondary" title="Copy"><i class="fa fa-copy"></i></a>
                                    <a href="" class="text-secondary" title="Delete"><i class="fa fa-trash"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container d-none">
            <div class="row">
                <main class="col-md-12 ms-sm-auto col-lg-12 pb-4 mb-2">
                    <div class="filepond-section-custom">
                        <input type="file"
                               class="filepond"
                               name="filepond"
                               id="filepond"
                               data-max-file-size="3MB"
                               data-max-files="3"/>
                    </div>
                </main>
            </div>
        </div>
    </section>

    <x-esign::modals.add-document />
</x-esign::layout-app>
