<x-esign::layout :title="$document->title" :documentId="$document->id">
    <section class="header-bottom-section">
        <div class="container-fluid">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-0"
            >
                <h4 class="h4">{{ $document->title }}</h4>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button
                            type="button"
                            class="btn btn-outline-dark"
                            data-bs-toggle="modal"
                            data-bs-target="#sendRecipientModal"
                        >
                            <i class="fas fa-user-plus"></i>
                            {{ __('esign::label.send') }}
                        </button>
                    </div>
                    <button
                        id="saveBtn"
                        type="button"
                        onclick="saveBtnAction()"
                        class="btn btn-primary d-flex align-items-center gap-1"
                    >
                        <i class="fas fa-save"></i>
                        {{ __('esign::label.save') }}
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="container-fluid">
        <div class="row">
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                @if ($document->document?->exists())
                    <div
                        id="pdfViewer"
                        data-url="{{ $document->document->url }}"
                    ></div>

                    @include('esign::documents.modals.send-mail-to-recipient', compact('document'))
                @else
                    @include('esign::partials.dropzone', ['page' => 'inner'])
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
                            @foreach (\NIIT\ESign\Enum\ElementType::withIcons(true) as $type => $data)
                                @php([$label, $icon] = $data)

                                <a
                                    href="javascript: void(0);"
                                    class="draggable icons-box-btn bg-white"
                                    data-type="{{ $type }}"
                                >
                                    <div
                                        class="flex items-center flex-col px-2 py-2"
                                    >
                                        <i class="{{ $icon }}"></i>
                                        <span class="text-xs mt-1">
                                            {{ $label }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('esign::partials.renderer')

    @pushonce('js')
        <script>
            const addedElements = [];

            $(() => {});
        </script>
    @endpushonce
</x-esign::layout>
