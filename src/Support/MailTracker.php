<?php

namespace NIIT\ESign\Support;

use Illuminate\Support\Str;
use NIIT\ESign\Models\DocumentSigner;

class MailTracker
{
    protected ?string $documentId;

    public function injectTrackingPixel(DocumentSigner $signer, $html)
    {
        $tracking_pixel = '<img border=0 width=1 alt="" height=1 src="'.route('esign.signing.mail-pixel', $signer).'" />';

        $linebreak = app(Str::class)->random(32);
        $html = str_replace("\n", $linebreak, $html);

        if (preg_match('/^(.*<body[^>]*>)(.*)$/', $html, $matches)) {
            $html = $matches[1].$matches[2].$tracking_pixel;
        } else {
            $html .= $tracking_pixel;
        }

        return str_replace($linebreak, "\n", $html);
    }
}
