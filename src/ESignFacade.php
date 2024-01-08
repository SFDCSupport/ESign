<?php

namespace NIIT\ESign;

use Illuminate\Support\Facades\Facade as Base;

/**
 * Class ESignFacade
 *
 * @mixin ESign
 */
class ESignFacade extends Base
{
    protected static function getFacadeAccessor(): string
    {
        return ESignServiceProvider::NAME;
    }
}
