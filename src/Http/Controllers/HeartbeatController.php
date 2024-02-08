<?php

namespace NIIT\ESign\Http\Controllers;

use Illuminate\Http\Request;

class HeartbeatController extends Controller
{
    public function __invoke(Request $request)
    {
        return $this->jsonResponse([
            'status' => 1,
        ]);
    }
}
