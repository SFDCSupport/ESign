<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Base;

abstract class Controller extends Base
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function user(?Request $request = null)
    {
        return ($request ?? auth())->user();
    }
}
