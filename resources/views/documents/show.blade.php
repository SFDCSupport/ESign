<x-esign::layout-app :title="$document->title" :documentId="$document->id">
    <div class="container-fluid">
        <div class="row">
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom"
                >
                    <h1 class="h2">{{ $document->title }}</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button
                                type="button"
                                class="btn btn-outline-dark"
                                data-bs-toggle="modal"
                                data-bs-target="#sendrecipientModal"
                            >
                                <i class="fas fa-user-plus"></i>
                                {{ __('esign::label.send') }}
                            </button>
                        </div>
                        <button
                            type="button"
                            class="btn btn-dark d-flex align-items-center gap-1"
                        >
                            <i class="fas fa-save"></i>
                            {{ __('esign::label.save') }}
                        </button>
                    </div>
                </div>

                @if ($document->document?->exists())
                    <div
                        id="pdfViewer"
                        data-url="{{ $document->document->url }}"
                    ></div>
                @else
                    @include('esign::documents.partials.filepond-uploader', ['page' => 'inner'])
                @endif
            </main>
            <div
                class="sidebar border border-right col-md-3 col-lg-2 p-0 bg-body-tertiary"
            >
                <div
                    class="offcanvas-md offcanvas-end bg-body-tertiary"
                    tabindex="-1"
                    id="sidebarMenu"
                    aria-labelledby="sidebarMenuLabel"
                >
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="sidebarMenuLabel">
                            Create New Document
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="offcanvas"
                            data-bs-target="#sidebarMenu"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div
                        class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto"
                    >
                        <div class="sel">
                            <div class="txt">First Party</div>
                            <div class="options hide">
                                <div
                                    class="option-party first-party-option"
                                    value=""
                                >
                                    <span class="bullet"></span>
                                    First Party
                                </div>
                                <div
                                    class="option-party second-party-option"
                                    value=""
                                >
                                    <span class="bullet"></span>
                                    Second Party
                                </div>
                                <div
                                    class="option-party third-party-option"
                                    value=""
                                >
                                    <span class="bullet"></span>
                                    Third Party
                                </div>
                            </div>
                        </div>
                        <div class="icons-box">
                            <a href="" class="icons-box-btn bg-white">
                                <div
                                    class="flex items-center flex-col px-2 py-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        strokeWidth="2"
                                        class="tabler-icon tabler-icon-text-size"
                                    >
                                        <path d="M3 7v-2h13v2"></path>
                                        <path d="M10 5v14"></path>
                                        <path d="M12 19h-4"></path>
                                        <path d="M15 13v-1h6v1"></path>
                                        <path d="M18 12v7"></path>
                                        <path d="M17 19h2"></path>
                                    </svg>
                                    <span class="text-xs mt-1">Text</span>
                                </div>
                            </a>
                            <a href="" class="icons-box-btn bg-white">
                                <div
                                    class="flex items-center flex-col px-2 py-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        strokeWidth="2"
                                        class="tabler-icon tabler-icon-writing-sign"
                                    >
                                        <path
                                            d="M3 19c3.333 -2 5 -4 5 -6c0 -3 -1 -3 -2 -3s-2.032 1.085 -2 3c.034 2.048 1.658 2.877 2.5 4c1.5 2 2.5 2.5 3.5 1c.667 -1 1.167 -1.833 1.5 -2.5c1 2.333 2.333 3.5 4 3.5h2.5"
                                        ></path>
                                        <path
                                            d="M20 17v-12c0 -1.121 -.879 -2 -2 -2s-2 .879 -2 2v12l2 2l2 -2z"
                                        ></path>
                                        <path d="M16 7h4"></path>
                                    </svg>
                                    <span class="text-xs mt-1">Signature</span>
                                </div>
                            </a>
                            <a href="" class="icons-box-btn bg-white">
                                <div
                                    class="flex items-center flex-col px-2 py-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        strokeWidth="2"
                                        class="tabler-icon tabler-icon-calendar-event"
                                    >
                                        <path
                                            d="M4 5m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"
                                        ></path>
                                        <path d="M16 3l0 4"></path>
                                        <path d="M8 3l0 4"></path>
                                        <path d="M4 11l16 0"></path>
                                        <path d="M8 15h2v2h-2z"></path>
                                    </svg>
                                    <span class="text-xs mt-1">Date</span>
                                </div>
                            </a>
                            <a href="" class="icons-box-btn bg-white">
                                <div
                                    class="flex items-center flex-col px-2 py-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        strokeWidth="2"
                                        class="tabler-icon tabler-icon-rubber-stamp"
                                    >
                                        <path
                                            d="M21 17.85h-18c0 -4.05 1.421 -4.05 3.79 -4.05c5.21 0 1.21 -4.59 1.21 -6.8a4 4 0 1 1 8 0c0 2.21 -4 6.8 1.21 6.8c2.369 0 3.79 0 3.79 4.05z"
                                        ></path>
                                        <path d="M5 21h14"></path>
                                    </svg>
                                    <span class="text-xs mt-1">Stamp</span>
                                </div>
                            </a>
                        </div>
                        <a href="" class="icons-box-btn bg-white">
                            <div class="flex items-center flex-col px-2 py-2">
                                <img src="images/clock.png" />
                                <span class="text-xs mt-1">Date</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-esign::layout-app>
