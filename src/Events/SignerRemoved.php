<?php
/*
 * @author : Anand Pilania
 * @mailto : Anand.Pilania@niit.com
 * @updated : 1/8/24, 4:39 PM
 */

namespace NIIT\ESign\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NIIT\ESign\Models\DocumentSigner;

class SignerRemoved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DocumentSigner $signer
    ) {
    }
}
