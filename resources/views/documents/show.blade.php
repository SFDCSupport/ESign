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

            <div class="col-sm-2">
                <div class="add-doc-sec">
          
                  <div class="edit-docs-file">
                    <img src="">
                    <a href="#" class="btn btn-sm btn-dark replace-doc-btn">Replace</a>
                    <a href="#" class="edit-docs-btn"><i class="fa fa-pen"></i></a>
                  </div>
          
                  <div class="flex pb-2 pt-1.5">
                    <div class="edit-doc-name">Partner Portal Guide.pdf 
                      <a href="#" class="edit-doc-text"><i class="fa fa-pen"></i></a>
                    </div>
                  </div>
        
                </div>
              </div>

            <main class="col-md-7 ms-sm-auto col-lg-7 px-md-4">
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
            <div class="sidebar border border-right col-md-3 col-lg-3 p-0 bg-body-tertiary">
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
                        <div class="select-party">
                            <div class="dropdown_c dropdown_click">
                                <div class="selecteddropdown">
                                    <span>First Party</span>
                                    <a href="#" class="add-party">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                                <div class="drop-content">
                                    <ul>
                                        <li>
                                            <a href="#" class="" value="">
                                                First Party
                                            </a>
                                            <a
                                                href="#"
                                                value=""
                                                class="deleted-party"
                                            >
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="" value="">
                                                Second Party
                                            </a>
                                            <a
                                                href="#"
                                                value=""
                                                class="deleted-party"
                                            >
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#" class="" value="">
                                                Third Party
                                            </a>
                                            <a
                                                href="#"
                                                value=""
                                                class="deleted-party"
                                            >
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </li>

                                            <a href="#" class="add-party-btn" value="">
                                             <i class="fa fa-user-plus"></i> &nbsp; Add <span>Fourth</span> Party
                                            </a>
                                       
                                    </ul>
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
