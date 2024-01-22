@php($i = $loop->iteration ?? 1)

<li
    class="signerLi @if(($loop->index ?? 0) === 0) selectedSigner @endif"
    data-signer-index="{{ $i }}"
>
    <input
        type="hidden"
        class="signerId"
        name="signer[{{ $i }}][id]"
        value="{{ $signer->id ?? null }}"
    />
    <input
        type="hidden"
        class="signerLabel"
        name="signer[{{ $i }}][label]"
        value="{{ $signer->label ?? null }}"
    />
    <input
        type="hidden"
        class="signerPosition"
        name="signer[{{ $i }}][position]"
        value="{{ $signer->position ?? $i }}"
    />

    <a href="javascript: void(0)" class="signerLabel">
        {{ $signer->label ?? __('esign::label.nth_signer', ['nth' => ordinal($i)]) }}
    </a>
    <div
        class="flex items-center space-x-1 deleted-updown-ele align-items-center"
    >
        <p class="updown-docs-btn partyReorder">
            <a href="javascript: void(0);" onclick="signerReorder(this, 'up')">
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
