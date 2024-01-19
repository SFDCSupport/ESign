<template id="addedElementTemplate">
    <div
        class="pos_rel auto-resizing-content addedElement __REQUIRED"
        data-element-signer-index="__SIGNER_INDEX"
        data-element-index="__POSITION"
        data-uuid="__UUID"
    >
        <input
            type="hidden"
            class="elementSignerId"
            name="signer[__SIGNER_INDEX][element][__POSITION][signer_id]"
            value="__SIGNER_ID"
        />
        <input
            type="hidden"
            class="elementType"
            name="signer[__SIGNER_INDEX][element][__POSITION][type]"
            value="__TYPE"
        />
        <input
            type="hidden"
            class="elementPosition"
            name="signer[__SIGNER_INDEX][element][__POSITION][position]"
            value="__POSITION"
        />
        <input
            type="hidden"
            class="elementLabel"
            name="signer[__SIGNER_INDEX][element][__POSITION][label]"
            value="__LABEL"
        />
        <input
            type="hidden"
            class="elementOffsetX"
            name="signer[__SIGNER_INDEX][element][__POSITION][offset_x]"
            value="__OFFSET_X"
        />
        <input
            type="hidden"
            class="elementOffsetY"
            name="signer[__SIGNER_INDEX][element][__POSITION][offset_y]"
            value="__OFFSET_Y"
        />
        <input
            type="hidden"
            class="elementWidth"
            name="signer[__SIGNER_INDEX][element][__POSITION][width]"
            value="__WIDTH"
        />
        <input
            type="hidden"
            class="elementHeight"
            name="signer[__SIGNER_INDEX][element][__POSITION][height]"
            value="__HEIGHT"
        />
        <input
            type="hidden"
            class="elementOnPage"
            name="signer[__SIGNER_INDEX][element][__POSITION][on_page]"
            value="__ON_PAGE"
        />

        <i class="__ICON type_icons"></i>
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
                <i class="fa fa-pen"></i>
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
                    name="signer[element][required]"
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
