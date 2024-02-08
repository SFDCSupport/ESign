Hi there,
<br />
<br />
Please check the copy of your "{{ $document->title }}" submission in the email
attachments.
<br />
<br />
Alternatively, you can review and download your copy using:
<br />
<br />
<a href="{{ $signer->getSignedDocumentUrl() }}">{{ $document->title }}</a>
<br />
<br />
Thanks,
<br />
{{ config('app.name') }}
