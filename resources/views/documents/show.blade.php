@php
    $isSigningRoute = request()->routeIs('esign.signing.*');
    $documentExists = $document->document?->exists();
@endphp

<x-esign::layout
    :title="$document->title"
    :documentId="$document->id"
    :isSigningRoute="$isSigningRoute"
>
    @pushonce('footJs')
        <script src="{{ url('vendor/esign/js/script.js') }}"></script>
    @endpushonce

    <section class="header-bottom-section @if($isSigningRoute) d-none @endif">
        <div class="container-fluid">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-0"
            >
                <h4 class="h4">{{ $document->title }}</h4>
                <div
                    class="btn-toolbar mb-2 mb-md-0 @if(!$documentExists) d-none @endif"
                >
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
            <div
                class="col-sm-2 dark-grey-bg @if($isSigningRoute || !$documentExists) d-none @endif"
            >
                <div class="add-doc-sec">
                    <div class="text-center mb-2">
                        <p class="mb-0" style="font-size: 10px">
                            {{ $document->title }}
                        </p>
                    </div>

                    <div class="text-center mb-2">
                        <a
                            href="javascript: void(0);"
                            id="documentReplaceBtn"
                            class="btn btn-sm btn-dark replace-doc-btn"
                        >
                            {{ __('esign::label.replace') }}
                        </a>

                        <a
                            href="javascript: void(0);"
                            class="edit-docs-btn"
                            id="documentRemoveBtn"
                        >
                            <i class="fa fa-times"></i>
                            Remove
                        </a>
                    </div>

                    <div class="edit-docs-file">
                        <div id="previewViewer"></div>
                    </div>
                </div>
            </div>

            <main
                class="@if($isSigningRoute || !$documentExists) col-12 @else col-md-7 ms-sm-auto col-lg-7 px-md-0 @endif"
            >
                @if ($documentExists)
                    <div
                        id="pdfViewer"
                        data-url="{{ $document->document->url }}"
                    ></div>

                    @include('esign::documents.modals.send-mail-to-recipient', compact('document'))
                @endif

                @php($dropZoneID = \Illuminate\Support\Str::random(12))

                @include('esign::partials.dropzone', [
                    'page' => 'inner',
                    'id' => $dropZoneID,
                    'hidden' => $documentExists
                ])
            </main>
            <div
                id="recipientsContainer"
                class="sidebar border border-right col-md-3 col-lg-3 p-0 bg-body-tertiary @if($isSigningRoute || !$documentExists) d-none @endif"
            >
                <form
                    id="recipientsForm"
                    action="{{ route('esign.documents.signers.store', $document) }}"
                    method="post"
                >
                    @csrf
                    <input
                        type="hidden"
                        name="document_id"
                        value="{{ $document->id }}"
                    />

                    <div
                        class="offcanvas-md offcanvas-end bg-body-tertiary"
                        tabindex="-1"
                        id="sidebarMenu"
                        aria-labelledby="sidebarMenuLabel"
                    >
                        <div class="offcanvas-header">
                            <h5
                                class="offcanvas-title"
                                id="sidebarMenuLabel"
                            ></h5>
                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="offcanvas"
                                data-bs-target="#sidebarMenu"
                                aria-label="{{ __('esign::label.close') }}"
                            ></button>
                        </div>
                        <div
                            class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto"
                        >
                            @php($hasSigners = ($totalSigners = $document->signers->count()) > 0)

                            <div class="select-party">
                                <div class="dropdown_c dropdown_click">
                                    <div class="selecteddropdown">
                                        <span
                                            class="selectedSigner"
                                            data-active-signer-index="1"
                                        >
                                            {{ __('esign::label.nth_signer', ['nth' => ordinal(1)]) }}
                                        </span>
                                        <a
                                            href="javascript: void(0);"
                                            class="add-party"
                                        >
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    </div>
                                    <div class="drop-content">
                                        <ul id="signerUl">
                                            @if ($hasSigners)
                                                @foreach ($document->signers as $signer)
                                                    @include('esign::documents.partials.signer', compact('signer'))
                                                @endforeach
                                            @else
                                                @include('esign::documents.partials.signer')
                                            @endif

                                            <a
                                                id="signerAdd"
                                                href="javascript: void(0)"
                                                class="add-party-btn"
                                                onclick="signerAdd()"
                                            ></a>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="editable-section addedElements"></div>

                            <div class="icons-box">
                                @foreach (\NIIT\ESign\Enum\ElementType::withIcons(true) as $type => $data)
                                    @php([$label, $icon] = $data)

                                    <a
                                        href="javascript: void(0);"
                                        class="draggable icons-box-btn bg-white elementType"
                                        data-type="{{ $type }}"
                                    >
                                        <span class="draggable-left-icon">
                                            <i class="fas fa-ellipsis-v"></i>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </span>
                                        <div
                                            class="flex items-center flex-col px-2 py-2"
                                        >
                                            <i
                                                class="{{ $icon }} elementIcon"
                                            ></i>
                                            <span class="text-xs mt-1">
                                                {{ $label }}
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('esign::partials.renderer')
    @include('esign::documents.partials.element-template')

    @pushonce('js')
        <script>
            const signerLi = () => $("#recipientsContainer li.signerLi");
            const totalSigners = () => signerLi().length;
            const highestSignerIndex = () => $("#recipientsContainer li.signerLi[data-signer-index]").highestData("signer-index");
            const signerAddedElements = () => $("#recipientsContainer .addedElements");
            const highestSignerElementIndex = () => $("#recipientsContainer div.addedElements div.addedElement").highestData("element-index");
            const getSignerElementTemplate = (uuid, position, label, icon, signerIndex = null, isRequired = true) => $.trim($("#addedElementTemplate").html())
                .replace(/__UUID/ig, uuid)
                .replace(/__POSITION/ig, position)
                .replace(/__LABEL/ig, $.trim(label))
                .replace(/__ICON/ig, icon)
                .replace(/__CHECKED/ig, isRequired ? "checked" : "")
                .replace(/__REQUIRED/ig, isRequired ? "required" : "")
                .replace(/__SIGNER_INDEX/ig, signerIndex || getActiveSignerIndex());
            const signerElementAdd = (uuid, type, signerIndex, label) => {
                const _highestElement = highestSignerElementIndex() + 1;
                const _icon = $(`#recipientsContainer a.elementType[data-type="${type}"] i.elementIcon`)
                    .attr("class")
                    .split(" ")
                    .filter(className => className !== "elementIcon")
                    .join(" ");

                signerAddedElements().append(
                    getSignerElementTemplate(uuid, _highestElement, label, _icon, signerIndex)
                );

                signerElementUpdate();
            };
            const signerElementUpdate = () => {
            };
            const signerElementRemove = (element) => {
                const _e = $(element);
                const _element = _e.hasClass("addedElements") ? element : _e.closest("div.addedElement");

                _element.remove();
                $(document).trigger("signer:element:removed", _element.attr("data-uuid"));

                signerElementUpdate();
            };
            const signerElementActive = (uuid) => {
                const addedElements = signerAddedElements();
                const uuidSelector = `div.addedElement[data-uuid="${uuid}"]`;

                if ((_ele = addedElements.find(`${uuidSelector}`)).hasClass("active")) {
                    _ele.removeClass("active");

                    return;
                }

                addedElements.find("div.addedElement.active").removeClass("active");
                addedElements.find(`${uuidSelector}:not(.active)`).addClass("active");
            };
            const signerElementToggleRequired = (element) => {
                const _t = $(element);

                _t.closest("div.addedElement").toggleClass("required", _t.prop("checked"));
            };
            const signerUpdate = () => {
                signerLi().find(".signerDelete,.signerReorder").toggleClass("d-none", totalSigners() <= 1);
                $("#signerAdd").html("<i class=\"fa fa-user-plus\"></i> " + '{!! __('esign::label.add_nth_signer') !!}'.replace(":nth", ordinal(highestSignerIndex() + 1)));
            };
            const signerReorder = (dir) => {
                console.log(dir);
            };
            const signerAdd = (index = null) => {
                const _highestSigner = index || highestSignerIndex() + 1;
                const clonedLi = $("li.signerLi:last").clone();
                clonedLi.removeClass("selectedSigner");
                clonedLi.find("input[type=\"hidden\"][name^=\"signer[\"]").each(function() {
                    const _t = $(this);
                    const _name = _t.attr("name");

                    _t.attr("name", _name.replace(/\[\d+\]/, "[" + _highestSigner + "]"));

                    if (_name.endsWith("[position]")) {
                        _t.val(_highestSigner);
                    }
                });
                const label = clonedLi.find("a.signerLabel").html(
                    ordinal(_highestSigner) + ' {{ __('esign::label.signer') }}'
                ).text();
                clonedLi.attr("data-signer-index", _highestSigner);
                clonedLi.insertAfter($("ul#signerUl li.signerLi:last"));

                $(document).trigger("signer:added", {
                    ...label,
                    from: "sidebar",
                    "signer-index": _highestSigner
                });

                signerUpdate();
            };
            const signerRemove = (signer) => {
                $(signer).closest("li.signerLi").remove();
                signerUpdate();
            };

            $(() => {
                @isset($dropZoneID)
                $(document).on("click", "#documentReplaceBtn", () => {
                    $('#{{ $dropZoneID }}').trigger("click");
                }).on("click", "#documentRemoveBtn", () => {
                    $.post('{{ route('esign.attachment.remove', ['attachment' => $document?->document?->id ?? '1']) }}', {
                        id: getDocumentId()
                    }).done((r) => {
                        const isSuccess = r.status;

                        if (isSuccess) {
                            location.reload(true);
                        }

                        toast(isSuccess ? "success" : "error", r.message || (isSuccess ? "Done" : "Error"));
                    }).fail((x) => {
                        toast("error", x.responseText);
                    });
                });
                @endisset

                $(document).on("signer:added", function(e, obj) {
                    if (obj.from === "sidebar") {
                        return;
                    }

                    signerUpdate(obj);
                })
                    .on("signer:update", function(e, obj) {
                        if (obj.from === "sidebar") {
                            return;
                        }

                        signerUpdate(obj);
                    })
                    .on("signer:removed", function(e, obj) {
                        if (obj.from === "sidebar") {
                            return;
                        }

                        signerUpdate();
                    })
                    .on("signer:element:set-active", function(e, obj) {
                        if(obj.from === 'sidebar') {
                            return;
                        }

                        obj.uuid && signerElementActive(obj.uuid)
                    })
                    .on("signer:element:added", function(e, obj) {
                        if (obj.from === "sidebar") {
                            return;
                        }

                        signerElementAdd(obj.uuid, obj.eleType, obj.signer_index, obj.text || obj.eleType);

                        if ($(`li.signerLi[data-signer-index="${obj.signer_index}"]`).length <= 0) {
                            signerAdd(obj.signer_index);
                        }
                    })
                    .on("signer:element:removed", function(e, obj) {
                        if(obj.from === 'sidebar') {
                            return;
                        }

                        if (obj.uuid && (_ele = signerAddedElements().find(`div.addedElement[data-uuid="${obj.uuid}"]`)).length > 0) {
                            signerElementRemove(_ele);
                        }
                    })
                    .on("signer:element:updated", function(e, obj) {
                        if(obj.from === 'sidebar') {
                            return;
                        }

                        signerElementUpdate(obj);
                    })
                    .on("elements-added-to-canvas", function(e) {
                        $(`#recipientsContainer .addedElement[data-element-signer-index!="1"]`).addClass("d-none");
                    })
                    .on("click", "#recipientsContainer li.signerLi a.signerLabel", function(e) {
                        const _t = $(this);
                        const _li = _t.closest("li.signerLi");
                        const index = _li.attr("data-signer-index");

                        $(".dropdown_click .drop-content ul").slideUp(100);
                        $(`#recipientsContainer .addedElement[data-element-signer-index!="${index}"]`).addClass("d-none");
                        $(`#recipientsContainer .addedElement[data-element-signer-index="${index}"]`).removeClass("d-none");

                        if (_li.hasClass("selectedSigner")) {
                            return;
                        }

                        signerLi().removeClass("selectedSigner");
                        _li.addClass("selectedSigner");

                        $("#recipientsContainer span.selectedSigner").text(_t.text())
                            .attr("data-active-signer-index", index);
                    }).on("signers-save", function(e) {
                    const form = $("#recipientsForm");
                    console.log(form.serializeArray());
                }).on("click", ".dropdown_click .selecteddropdown", function(e) {
                    $(".dropdown_click .drop-content ul").slideToggle();
                });

                $(document).on("click", "#expand-form-button", () => {
                    $('#form-container').removeClass('d-none');
                    $(this).addClass('d-none');
                });

                $(document).on("click", "#minimize-form-button", () => {
                    $('#form-container').addClass('d-none');
                    $('#expand-form-button').removeClass('d-none');
                });

                partyUpdate();
                signerUpdate();
            });
        </script>
    @endpushonce
</x-esign::layout>
