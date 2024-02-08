Hi there,
<br />
<br />
You have been invited to submit the "{{ $document->title }}" document.
<br />
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
<br />
Please contact us by replying to this email if you didn't request this.
<br />
<br />
Thanks,
{{ config('app.name') }}
