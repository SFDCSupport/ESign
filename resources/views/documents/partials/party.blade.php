<li>
    <a href="javascript: void(0)" id="partyLabel">
        {{ $signer->label ?? ordinal($loop->iteration ?? 1).' '.__('esign::label.party') }}
    </a>
    <a
        id="partyDelete"
        data-party="{{ $loop->index ?? 0 }}"
        href="javascript: void(0)"
        class="deleted-party"
    >
        <i class="fa fa-trash"></i>
    </a>
</li>
