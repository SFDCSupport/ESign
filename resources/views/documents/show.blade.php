<x-esign::layout :title="$document->title" :documentId="$document->id">
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

                                <a href="" class="icons-box-btn bg-white">
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
