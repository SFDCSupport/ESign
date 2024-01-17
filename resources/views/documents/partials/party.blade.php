<li class="partyLi">
    <a href="javascript: void(0)" class="partyLabel">
        {{ $signer->label ?? __('esign::label.nth_party', ['nth' => ordinal($loop->iteration ?? 1)]) }}
    </a>
    <div class="flex items-center space-x-1 deleted-updown-ele align-items-center">
     <a href="#" class="updown-docs-btn">
        <i class="fa-solid fa-arrows-up-down"></i>    
    </a>    
    <a
        data-party="{{ $loop->iteration ?? 1 }}"
        href="javascript: void(0)"
        class="deleted-party partyDelete"
        onclick="partyRemove(this)"
    >
        <i class="fa fa-trash"></i>
    </a>
</div>
</li>
