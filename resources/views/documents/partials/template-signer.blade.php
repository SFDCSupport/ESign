<template id="signerTemplate">
    <li class="signerLi" data-signer-index="__INDEX" data-signer-uuid="__UUID">
        <a href="javascript: void(0)" class="signerLabel">__LABEL</a>
        <div
            class="flex items-center space-x-1 deleted-updown-ele align-items-center"
        >
            <p class="updown-docs-btn partyReorder">
                <a
                    href="javascript: void(0);"
                    onclick="signerReorder(this, 'up')"
                >
                    <i class="fas fa-caret-up"></i>
                </a>
                <a
                    href="javascript: void(0);"
                    onclick="signerReorder(this, 'down')"
                >
                    <i class="fas fa-caret-down"></i>
                </a>
            </p>
            <a
                href="javascript: void(0)"
                class="deleted-party signerDelete"
                onclick="signerRemove(this)"
            >
                <i class="fa fa-trash"></i>
            </a>
        </div>
    </li>
</template>
