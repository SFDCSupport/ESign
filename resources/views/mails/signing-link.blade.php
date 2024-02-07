{{ $document->title }}
<br />
{{ $signer->signingUrl() }}
<br />
<img
    border="0"
    width="1"
    alt=""
    height="1"
    src="{{ route('esign.signing.mail-pixel', $signer) }}"
/>
