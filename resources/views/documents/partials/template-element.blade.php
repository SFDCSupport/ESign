<template id="addedElementTemplate">
    <div
        class="pos_rel auto-resizing-content addedElement __REQUIRED"
        data-element-signer-uuid="__SIGNER_UUID"
        data-element-index="__POSITION"
        data-uuid="__UUID"
    >
        <i class="__ICON type_icons"></i>
        <div class="relative overflow-visible d-flex align-items-center">
            <span
                dir="auto"
                contenteditable="false"
                class="inline peer contenteditable-content outline-none focus:block"
                style="min-width: 2px"
            >
                __LABEL
            </span>
            <span
                class="edit-resizing-btn contentEditable"
                data-content-editable="span"
                data-content-editable-key="signers.elements.text"
            >
                <em class="fa-solid fa-pen"></em>
            </span>
        </div>
        <div
            class="flex items-center space-x-1 deleted-required-ele align-items-center"
        >
            <div class="form-check form-switch">
                <input
                    onclick="signerElementToggleRequired(this)"
                    class="form-check-input elementRequired"
                    type="checkbox"
                    role="switch"
                    __CHECKED
                />
            </div>
            <a
                onclick="signerElementRemove(this)"
                href="javascript: void(0);"
                class="removecontenteditable removeAddedElement"
            >
                <i class="fa fa-trash"></i>
            </a>
        </div>
    </div>
</template>
