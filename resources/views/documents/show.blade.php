@php
    $isSigningRoute = request()->routeIs('esign.signing.*');
    $documentExists = $document->document?->exists();
@endphp

<x-esign::layout
    :title="$document->title"
    :document="$document"
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
                <div
                    class="offcanvas-md offcanvas-end bg-body-tertiary"
                    tabindex="-1"
                    id="sidebarMenu"
                    aria-labelledby="sidebarMenuLabel"
                >
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="sidebarMenuLabel"></h5>
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
                                        data-active-signer
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
                                    </ul>
                                    <a
                                        id="signerAdd"
                                        href="javascript: void(0)"
                                        class="add-party-btn"
                                        onclick="signerAdd()"
                                    ></a>
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
                                        <i class="{{ $icon }} elementIcon"></i>
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
    @include('esign::documents.partials.element-template')

    @pushonce('js')
        <script>
            const signerLi = () => $("#recipientsContainer li.signerLi");
            const totalSigners = () => signerLi().length;
            const highestSignerIndex = () => $("#recipientsContainer li.signerLi[data-signer-index]").highestData("signer-index");
            const signerAddedElements = () => $("#recipientsContainer .addedElements");
            const highestSignerElementIndex = () => $("#recipientsContainer div.addedElements div.addedElement").highestData("element-index");
            const getSignerElementTemplate = (obj, position, icon) => $.trim($("#addedElementTemplate").html())
                .replace(/__UUID/ig, obj.uuid)
                .replace(/__POSITION/ig, position)
                .replace(/__LABEL/ig, $.trim(obj.label))
                .replace(/__ICON/ig, icon)
                .replace(/__CHECKED/ig, obj.is_required ? "checked" : "")
                .replace(/__REQUIRED/ig, obj.is_required ? "required" : "")
                .replace(/__SIGNER_UUID/ig, obj.signer_uuid || getActiveSigner());
            const signerElementAdd = (obj) => {
                const _highestElement = highestSignerElementIndex() + 1;
                const _icon = $(`#recipientsContainer a.elementType[data-type="${obj.eleType}"] i.elementIcon`)
                    .attr("class")
                    .split(" ")
                    .filter(className => className !== "elementIcon")
                    .join(" ");

                signerAddedElements().append(
                    getSignerElementTemplate(obj, _highestElement, _icon)
                );

                signerElementUpdate();
            };
            const signerElementUpdate = () => {
            };
            const signerElementRemove = (element) => {
                const _e = $(element);
                const _element = _e.hasClass("addedElements") ? element : _e.closest("div.addedElement");

                _element.remove();
                $(document).trigger("signer:element:removed", {
                    from: "sidebar",
                    uuid: _element.attr("data-uuid"),
                    signer_uuid: _element.attr("data-element-signer-uuid")
                });

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
                const isRequired = _t.prop("checked");
                const _element = _t.closest("div.addedElement");

                _element.toggleClass("required", isRequired);

                $(document).trigger("signer:element:updated", {
                    from: "sidebar",
                    uuid: _element.attr("data-uuid"),
                    signer_uuid: _element.attr("data-element-signer-uuid"),
                    is_required: isRequired
                });
            };
            const signerUpdate = () => {
                signerLi().find(".signerDelete,.signerReorder").toggleClass("d-none", totalSigners() <= 1);
                $("#signerAdd").html("<i class=\"fa fa-user-plus\"></i> " + '{!! __('esign::label.add_nth_signer') !!}'.replace(":nth", ordinal(highestSignerIndex() + 1)));

                const signerUl = $("ul#signerUl");

                signerUl.find("li.signerLi .partyReorder a").removeClass("d-none");
                signerUl.find("li.signerLi:first .partyReorder a:first").addClass("d-none");
                signerUl.find("li.signerLi:last .partyReorder a:last").addClass("d-none");
            };
            const signerReorder = (ele, dir) => {
                const signerLi = $(ele).closest("li.signerLi");
                const isUp = (dir === "up");

                if (
                    (signerLi.is("li:first-child") && isUp) ||
                    (signerLi.is("li:last-child") && !isUp)
                ) {
                    return;
                }

                const ownIndex = signerLi.attr("data-signer-index");
                const swapWith = signerLi[isUp ? "prev" : "next"]();
                const swapWithIndex = swapWith.attr("data-signer-index");
                const detachedLi = signerLi.detach();

                detachedLi[isUp ? "insertBefore" : "insertAfter"](swapWith);
                detachedLi.attr("data-signer-index", swapWithIndex);
                swapWith.attr("data-signer-index", ownIndex);

                signerUpdate();

                $(document).trigger("signer:reordered", {
                    uuid: signerLi.attr("data-signer-uuid"),
                    withUuid: swapWith.attr("data-signer-uuid"),
                    index: ownIndex,
                    withIndex: swapWithIndex
                });
            };
            const signerAdd = (obj = null) => {
                console.log(obj);
                const _highestSigner = obj?.signer_index || obj?.index || highestSignerIndex() + 1;
                const clonedLi = $("li.signerLi:last").clone();
                const lastSigner = $("ul#signerUl li.signerLi:last");
                const uuid = obj?.signer_uuid || obj?.uuid || generateUniqueId("s_");

                const label = obj?.signer_label || obj?.label || ordinal(_highestSigner) + ' {{ __('esign::label.signer') }}';
                clonedLi.removeClass("selectedSigner");
                clonedLi.find("a.signerLabel").html(label);
                clonedLi.attr("data-signer-index", _highestSigner);
                clonedLi.attr("data-signer-uuid", uuid);
                clonedLi.insertAfter(lastSigner);

                if (obj?.from !== "loadedObject") {
                    $(document).trigger("signer:added", {
                        label,
                        uuid,
                        from: "sidebar",
                        "signer_index": _highestSigner
                    });
                }

                signerUpdate();
            };
            const signerRemove = (signer) => {
                const signerLi = $(signer).closest("li.signerLi");
                const uuid = signerLi.attr("data-signer-uuid");

                signerLi.remove();

                $(`div.addedElement[data-element-signer-uuid="${uuid}"] a.removeAddedElement`).each(function() {
                    $(this).trigger("click");
                });

                $(document).trigger("signer:removed", {
                    ...uuid,
                    from: "sidebar"
                });

                signerUpdate();

                if(getActiveSigner() === uuid) {
                    $('ul#signerUl li.signerLi:first a.signerLabel').trigger('click');
                }
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

                $(document).on("signer:add", function(e, obj) {
                    if (obj.from === "sidebar") {
                        return;
                    }

                    signerAdd(obj);
                }).on("signer:added", function(e, obj) {
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
                        if (obj.from === "sidebar") {
                            return;
                        }

                        obj.uuid && signerElementActive(obj.uuid);
                    })
                    .on("signer:element:added", function(e, obj) {
                        if (obj.from === "sidebar") {
                            return;
                        }

                        obj.label = obj.text || obj.eleType;

                        if ((_li = $("#signerUl li.signerLi")).length === 1 && _li.attr("data-signer-uuid") === undefined) {
                            _li.attr("data-signer-uuid", obj.signer_uuid);
                            $("#recipientsContainer span.selectedSigner").attr("data-active-signer", obj.signer_uuid);
                        } else if ($(`#signerUl li.signerLi[data-signer-uuid="${obj.signer_uuid}"]`).length <= 0) {
                            signerAdd(obj);
                        }

                        signerElementAdd(obj);
                    })
                    .on("signer:element:removed", function(e, obj) {
                        if (obj.from === "sidebar") {
                            return;
                        }

                        if (obj.uuid && (_ele = signerAddedElements().find(`div.addedElement[data-uuid="${obj.uuid}"]`)).length > 0) {
                            signerElementRemove(_ele);
                        }
                    })
                    .on("signer:element:updated", function(e, obj) {
                        if (obj.from === "sidebar") {
                            return;
                        }

                        signerElementUpdate(obj);
                    })
                    .on("elements-added-to-canvas", function(e) {
                        $(`#recipientsContainer .addedElement[data-element-signer-uuid!="${getActiveSigner()}"]`).addClass("d-none");
                    })
                    .on("click", "#recipientsContainer li.signerLi a.signerLabel", function(e) {
                        const _t = $(this);
                        const _li = _t.closest("li.signerLi");
                        const uuid = _li.attr("data-signer-uuid");

                        $(".dropdown_click .drop-content ul").slideUp(100);
                        $(`#recipientsContainer .addedElement[data-element-signer-uuid!="${uuid}"]`).addClass("d-none");
                        $(`#recipientsContainer .addedElement[data-element-signer-uuid="${uuid}"]`).removeClass("d-none");

                        if (_li.hasClass("selectedSigner")) {
                            return;
                        }

                        signerLi().removeClass("selectedSigner");
                        _li.addClass("selectedSigner");

                        $("#recipientsContainer span.selectedSigner").text(_t.text())
                            .attr("data-active-signer", uuid);
                    }).on("signers-save", function(e) {
                    e.preventDefault();

                    setTimeout(() => $(document).trigger("loader:show"), 0);

                    $.post('{{ route('esign.documents.signers.store', $document) }}', $.extend({}, { signers: loadedData }, {
                        _token: '{{ csrf_token() }}',
                        document_id: '{{ $document->id }}',
                        mode: "create"
                    })).done((r) => {
                        if (r.data) {
                            $(document).trigger("process-ids", r.data);
                        }

                        $(document).trigger("loader:hide");
                    }).fail((x) => {
                        toast("error", x.responseText);
                        $(document).trigger("loader:hide");
                    });
                }).on("click", ".dropdown_click .selecteddropdown", function(e) {
                    $(".dropdown_click .drop-content ul").slideToggle();
                });

                signerUpdate();
            });
        </script>
    @endpushonce
</x-esign::layout>
