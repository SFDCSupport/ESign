<li
    class="partyLi @if(($loop->index ?? 0) === 0) selectedParty @endif"
    data-party-index="{{ $loop->iteration ?? 1 }}"
>
    <a href="javascript: void(0)" class="partyLabel">
        {{ $signer->label ?? __('esign::label.nth_party', ['nth' => ordinal($loop->iteration ?? 1)]) }}
    </a>
    <div
        class="flex items-center space-x-1 deleted-updown-ele align-items-center"
    >
        <a href="javascript: void(0);" class="updown-docs-btn partyReorder">
            <i class="fa-solid fa-arrows-up-down"></i>
        </a>
        <a
            href="javascript: void(0)"
            class="deleted-party partyDelete"
            onclick="partyRemove(this)"
        >
            <i class="fa fa-trash"></i>
        </a>
    </div>
</li>
