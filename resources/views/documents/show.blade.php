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
                class="col-sm-2 @if($isSigningRoute || !$documentExists) d-none @endif"
            >
                <div class="add-doc-sec">
                    <div class="edit-docs-file">
                        <div id="previewViewer"></div>
                        <a
                            href="javascript: void(0);"
                            id="replaceBtn"
                            class="btn btn-sm btn-dark replace-doc-btn"
                        >
                            {{ __('esign::label.replace') }}
                        </a>

                        <a href="javascript: void(0);" class="edit-docs-btn">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                    <div class="flex pb-2 pt-1.5">
                        <div class="edit-doc-name">
                            {{ $document->title }}
                            <a
                                href="javascript: void(0);"
                                class="edit-doc-text"
                            >
                                <i class="fa fa-pen"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <main
                class="@if($isSigningRoute || !$documentExists) col-12 @else col-md-7 ms-sm-auto col-lg-7 px-md-4 @endif"
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
                            aria-label="Close"
                        ></button>
                    </div>
                    <div
                        class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto"
                    >
                        <div class="select-party">
                            <div class="dropdown_c dropdown_click">
                                <div class="selecteddropdown">
                                    <span
                                        class="selectedParty"
                                        data-active-party="1"
                                    >
                                        {{ __('esign::label.nth_party', ['nth' => ordinal(1)]) }}
                                    </span>
                                    <a
                                        href="javascript: void(0);"
                                        class="add-party"
                                    >
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                                <div class="drop-content">
                                    <ul id="partyUl">
                                        @php($hasSigners = ($totalSigners = $document->signers->count()) > 0)

                                        @if ($hasSigners)
                                            @foreach ($document->signers as $signer)
                                                @include('esign::documents.partials.party', compact('signer'))
                                            @endforeach
                                        @else
                                            @include('esign::documents.partials.party')
                                        @endif

                                        <a
                                            id="partyAdd"
                                            href="javascript: void(0)"
                                            class="add-party-btn"
                                            onclick="partyAdd()"
                                        ></a>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="editable-section addedElements">
                            <template id="addedElementTemplate">
                                <div
                                    class="pos_rel auto-resizing-content addedElement __REQUIRED"
                                    data-element="__INDEX"
                                    data-uuid="__UUID"
                                >
                                    <i class="fa fa-font type_icons"></i>
                                    <div
                                        class="group/contenteditable relative overflow-visible d-flex align-items-center"
                                    >
                                        <span
                                            dir="auto"
                                            contenteditable="false"
                                            class="inline peer contenteditable-content outline-none focus:block"
                                            style="min-width: 2px"
                                        >
                                            __LABEL
                                        </span>
                                        <span class="edit-resizing-btn">
                                            <i class="__ICON"></i>
                                        </span>
                                    </div>
                                    <div
                                        class="flex items-center space-x-1 deleted-required-ele align-items-center"
                                    >
                                        <div class="form-check form-switch">
                                            <input
                                                onclick="partyElementToggleRequired(this)"
                                                class="form-check-input"
                                                type="checkbox"
                                                role="switch"
                                                name="required"
                                                __CHECKED
                                            />
                                        </div>
                                        <a
                                            onclick="partyElementRemove(this)"
                                            href="javascript: void(0);"
                                            class="removecontenteditable removeAddedElement"
                                        >
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>

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

    @pushonce('js')
        <script>
            const partyLi = () => $("#recipientsContainer li.partyLi");
            const totalParties = () => partyLi().length;
            const highestParty = () => $("#recipientsContainer li.partyLi a[data-party]").highestData("party");
            const partyAddedElements = () => $("#recipientsContainer .addedElements");
            const highestPartyElement = () => $("#recipientsContainer div.addedElements div.addedElement").highestData("element");
            const getPartyElementTemplate = (uuid, index, label, icon, isRequired = true) => $.trim($("#addedElementTemplate").html())
                .replace(/__UUID/ig, uuid)
                .replace(/__INDEX/ig, index)
                .replace(/__LABEL/ig, label)
                .replace(/__ICON/ig, icon)
                .replace(/__CHECKED/ig, isRequired ? "checked" : "")
                .replace(/__REQUIRED/ig, isRequired ? "required" : "");
            const partyElementAdd = (uuid, type, label) => {
                const _highestElement = highestPartyElement() + 1;
                const _icon = $(`#recipientsContainer a.elementType[data-type="${type}"] i.elementIcon`)
                    .attr("class")
                    .split(" ")
                    .filter(className => className !== "elementIcon");

                partyAddedElements().append(
                    getPartyElementTemplate(uuid, _highestElement, label, _icon)
                );

                partyElementUpdate();
            };
            const partyElementUpdate = () => {
            };
            const partyElementRemove = (element) => {
                const _e = $(element);
                const _element = _e.hasClass("addedElements") ? element : _e.closest("div.addedElement");

                _element.remove();
                $(document).trigger("party-element:remove", _element.attr("data-uuid"));

                partyElementUpdate();
            };
            const partyElementActive = (uuid) => {
                const addedElements = partyAddedElements();
                const uuidSelector = `div.addedElement[data-uuid="${uuid}"]`;

                if ((_ele = addedElements.find(`${uuidSelector}`)).hasClass("active")) {
                    _ele.removeClass("active");

                    return;
                }

                addedElements.find("div.addedElement.active").removeClass("active");
                addedElements.find(`${uuidSelector}:not(.active)`).addClass("active");
            };
            const partyElementToggleRequired = (element) => {
                const _t = $(element);

                _t.closest("div.addedElement").toggleClass("required", _t.prop("checked"));
            };
            const partyUpdate = () => {
                partyLi().find(".partyDelete,.partyReorder").toggleClass("d-none", totalParties() <= 1);
                $("#partyAdd").html("<i class=\"fa fa-user-plus\"></i> " + '{!! __('esign::label.add_nth_party') !!}'.replace(":nth", ordinal(highestParty() + 1)));
            };
            const partyAdd = () => {
                const _highestParty = highestParty() + 1;
                const clonedLi = $("li.partyLi:last").clone();
                clonedLi.removeClass("selectedParty");
                clonedLi.find("a.partyLabel").html(
                    ordinal(_highestParty) + ' {{ __('esign::label.party') }}'
                );
                clonedLi.find("a[data-party]").attr("data-party", _highestParty);
                clonedLi.insertAfter($("ul#partyUl li.partyLi:last"));

                partyUpdate();
            };
            const partyRemove = (party) => {
                $(party).closest("li.partyLi").remove();
                partyUpdate();
            };

            $(() => {
                @isset($dropZoneID)
                $(document).on("click", "#replaceBtn", () => {
                    $('#{{ $dropZoneID }}').trigger("click");
                });
                @endisset

                $(document).on("party:add", (e, label) => partyUpdate(label))
                    .on("party:update", partyUpdate)
                    .on("party:remove", partyUpdate)
                    .on("party-element:active", (e, uuid = null) => uuid && partyElementActive(uuid))
                    .on("party-element:add", (e, data) => partyElementAdd(data.uuid, data.eleType, data.text || data.eleType))
                    .on("party-element:remove", (e, uuid = null) => {
                        if (uuid && (_ele = partyAddedElements().find(`div.addedElement[data-uuid="${uuid}"]`)).length > 0) {
                            partyElementRemove(_ele);
                        }
                    })
                    .on("party-element:update", partyElementAdd)
                    .on("click", "#recipientsContainer a.partyLabel", function() {
                        const _t = $(this);
                        const _li = _t.closest("li.partyLi");

                        if (_li.hasClass("selectedParty")) {
                            return;
                        }

                        partyLi().removeClass("selectedParty");
                        _li.addClass("selectedParty");

                        $("#recipientsContainer span.selectedParty").text(_t.text())
                            .data("active-party", _li.find("a[data-party]").attr("data-party"));
                    });

                partyUpdate();
            });
        </script>
    @endpushonce
</x-esign::layout>
