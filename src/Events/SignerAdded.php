<?php
/*
 * @author : Anand Pilania
 * @mailto : Anand.Pilania@niit.com
 * @updated : 1/8/24, 4:39 PM
 */

namespace NIIT\ESign\Events\Signer;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NIIT\ESign\Models\Signer;

class SignerAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Signer $signer
    ) {
    }
}
